<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use GuzzleHttp\ClientInterface;
use Kreait\Firebase\Auth\ActionCodeSettings;
use Kreait\Firebase\Auth\ActionCodeSettings\ValidatedActionCodeSettings;
use Kreait\Firebase\Auth\ApiClient;
use Kreait\Firebase\Auth\CreateActionLink;
use Kreait\Firebase\Auth\CreateSessionCookie;
use Kreait\Firebase\Auth\CustomTokenViaGoogleIam;
use Kreait\Firebase\Auth\DeleteUsersRequest;
use Kreait\Firebase\Auth\DeleteUsersResult;
use Kreait\Firebase\Auth\SendActionLink;
use Kreait\Firebase\Auth\SendActionLink\FailedToSendActionLink;
use Kreait\Firebase\Auth\SignIn\FailedToSignIn;
use Kreait\Firebase\Auth\SignIn\Handler as SignInHandler;
use Kreait\Firebase\Auth\SignInAnonymously;
use Kreait\Firebase\Auth\SignInResult;
use Kreait\Firebase\Auth\SignInWithCustomToken;
use Kreait\Firebase\Auth\SignInWithEmailAndOobCode;
use Kreait\Firebase\Auth\SignInWithEmailAndPassword;
use Kreait\Firebase\Auth\SignInWithIdpCredentials;
use Kreait\Firebase\Auth\SignInWithRefreshToken;
use Kreait\Firebase\Auth\UserRecord;
use Kreait\Firebase\Exception\Auth\AuthError;
use Kreait\Firebase\Exception\Auth\FailedToVerifySessionCookie;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Kreait\Firebase\Exception\Auth\RevokedIdToken;
use Kreait\Firebase\Exception\Auth\RevokedSessionCookie;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\JWT\CustomTokenGenerator;
use Kreait\Firebase\JWT\IdTokenVerifier;
use Kreait\Firebase\JWT\SessionCookieVerifier;
use Kreait\Firebase\JWT\Value\Duration;
use Kreait\Firebase\Util\DT;
use Kreait\Firebase\Util\JSON;
use Kreait\Firebase\Value\ClearTextPassword;
use Kreait\Firebase\Value\Email;
use Kreait\Firebase\Value\Uid;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\UnencryptedToken;
use Psr\Clock\ClockInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Traversable;

/**
 * @internal
 */
final class Auth implements Contract\Auth
{
    private ApiClient $client;
    private ClientInterface $httpClient;

    /** @var CustomTokenGenerator|CustomTokenViaGoogleIam|null */
    private $tokenGenerator;
    private IdTokenVerifier $idTokenVerifier;
    private SessionCookieVerifier $sessionCookieVerifier;
    private SignInHandler $signInHandler;
    private ?string $tenantId;
    private string $projectId;
    private ClockInterface $clock;

    /**
     * @param CustomTokenGenerator|CustomTokenViaGoogleIam|null $tokenGenerator
     */
    public function __construct(
        ApiClient $client,
        ClientInterface $httpClient,
        $tokenGenerator,
        IdTokenVerifier $idTokenVerifier,
        SessionCookieVerifier $sessionCookieVerifier,
        SignInHandler $signInHandler,
        string $projectId,
        ?string $tenantId,
        ClockInterface $clock
    ) {
        $this->client = $client;
        $this->httpClient = $httpClient;
        $this->tokenGenerator = $tokenGenerator;
        $this->idTokenVerifier = $idTokenVerifier;
        $this->sessionCookieVerifier = $sessionCookieVerifier;
        $this->signInHandler = $signInHandler;
        $this->tenantId = $tenantId;
        $this->projectId = $projectId;
        $this->clock = $clock;
    }

    public function getUser($uid): UserRecord
    {
        $uid = (string) (new Uid((string) $uid));

        $userRecord = $this->getUsers([$uid])[$uid] ?? null;

        if ($userRecord !== null) {
            return $userRecord;
        }

        throw new UserNotFound("No user with uid '{$uid}' found.");
    }

    public function getUsers(array $uids): array
    {
        $uids = \array_map(static fn ($uid) => (string) (new Uid((string) $uid)), $uids);

        $users = \array_fill_keys($uids, null);

        $response = $this->client->getAccountInfo($uids);

        $data = JSON::decode((string) $response->getBody(), true);

        foreach ($data['users'] ?? [] as $userData) {
            $userRecord = UserRecord::fromResponseData($userData);
            $users[$userRecord->uid] = $userRecord;
        }

        return $users;
    }

    public function listUsers(int $maxResults = 1000, int $batchSize = 1000): Traversable
    {
        $pageToken = null;
        $count = 0;

        do {
            $response = $this->client->downloadAccount($batchSize, $pageToken);
            $result = JSON::decode((string) $response->getBody(), true);

            foreach ((array) ($result['users'] ?? []) as $userData) {
                yield UserRecord::fromResponseData($userData);

                if (++$count === $maxResults) {
                    return;
                }
            }

            $pageToken = $result['nextPageToken'] ?? null;
        } while ($pageToken);
    }

    public function createUser($properties): UserRecord
    {
        $request = $properties instanceof Request\CreateUser
            ? $properties
            : Request\CreateUser::withProperties($properties);

        $response = $this->client->createUser($request);

        return $this->getUserRecordFromResponse($response);
    }

    public function updateUser($uid, $properties): UserRecord
    {
        $request = $properties instanceof Request\UpdateUser
            ? $properties
            : Request\UpdateUser::withProperties($properties);

        $request = $request->withUid($uid);

        $response = $this->client->updateUser($request);

        return $this->getUserRecordFromResponse($response);
    }

    public function createUserWithEmailAndPassword($email, $password): UserRecord
    {
        return $this->createUser(
            Request\CreateUser::new()
                ->withUnverifiedEmail($email)
                ->withClearTextPassword($password)
        );
    }

    public function getUserByEmail($email): UserRecord
    {
        $email = (string) (new Email((string) $email));

        $response = $this->client->getUserByEmail($email);

        $data = JSON::decode((string) $response->getBody(), true);

        if (empty($data['users'][0])) {
            throw new UserNotFound("No user with email '{$email}' found.");
        }

        return UserRecord::fromResponseData($data['users'][0]);
    }

    public function getUserByPhoneNumber($phoneNumber): UserRecord
    {
        $phoneNumber = (string) $phoneNumber;

        $response = $this->client->getUserByPhoneNumber($phoneNumber);

        $data = JSON::decode((string) $response->getBody(), true);

        if (empty($data['users'][0])) {
            throw new UserNotFound("No user with phone number '{$phoneNumber}' found.");
        }

        return UserRecord::fromResponseData($data['users'][0]);
    }

    public function createAnonymousUser(): UserRecord
    {
        return $this->createUser(Request\CreateUser::new());
    }

    public function changeUserPassword($uid, $newPassword): UserRecord
    {
        return $this->updateUser($uid, Request\UpdateUser::new()->withClearTextPassword($newPassword));
    }

    public function changeUserEmail($uid, $newEmail): UserRecord
    {
        return $this->updateUser($uid, Request\UpdateUser::new()->withEmail($newEmail));
    }

    public function enableUser($uid): UserRecord
    {
        return $this->updateUser($uid, Request\UpdateUser::new()->markAsEnabled());
    }

    public function disableUser($uid): UserRecord
    {
        return $this->updateUser($uid, Request\UpdateUser::new()->markAsDisabled());
    }

    public function deleteUser($uid): void
    {
        $uid = (string) (new Uid((string) $uid));

        try {
            $this->client->deleteUser($uid);
        } catch (UserNotFound $e) {
            throw new UserNotFound("No user with uid '{$uid}' found.");
        }
    }

    public function deleteUsers(iterable $uids, bool $forceDeleteEnabledUsers = false): DeleteUsersResult
    {
        $request = DeleteUsersRequest::withUids($this->projectId, $uids, $forceDeleteEnabledUsers);

        $response = $this->client->deleteUsers(
            $request->projectId(),
            $request->uids(),
            $request->enabledUsersShouldBeForceDeleted(),
            $this->tenantId
        );

        return DeleteUsersResult::fromRequestAndResponse($request, $response);
    }

    public function getEmailActionLink(string $type, $email, $actionCodeSettings = null, ?string $locale = null): string
    {
        $email = (string) (new Email((string) $email));

        if ($actionCodeSettings === null) {
            $actionCodeSettings = ValidatedActionCodeSettings::empty();
        } else {
            $actionCodeSettings = $actionCodeSettings instanceof ActionCodeSettings
                ? $actionCodeSettings
                : ValidatedActionCodeSettings::fromArray($actionCodeSettings);
        }

        return (new CreateActionLink\GuzzleApiClientHandler($this->httpClient, $this->projectId))
            ->handle(CreateActionLink::new($type, $email, $actionCodeSettings, $this->tenantId, $locale))
        ;
    }

    public function sendEmailActionLink(string $type, $email, $actionCodeSettings = null, ?string $locale = null): void
    {
        $email = (string) (new Email((string) $email));

        if ($actionCodeSettings === null) {
            $actionCodeSettings = ValidatedActionCodeSettings::empty();
        } else {
            $actionCodeSettings = $actionCodeSettings instanceof ActionCodeSettings
                ? $actionCodeSettings
                : ValidatedActionCodeSettings::fromArray($actionCodeSettings);
        }

        $createAction = CreateActionLink::new($type, $email, $actionCodeSettings, $this->tenantId, $locale);
        $sendAction = new SendActionLink($createAction, $locale);

        if (\mb_strtolower($type) === 'verify_email') {
            // The Firebase API expects an ID token for the user belonging to this email address
            // see https://github.com/firebase/firebase-js-sdk/issues/1958
            try {
                $user = $this->getUserByEmail($email);
            } catch (Throwable $e) {
                throw new FailedToSendActionLink($e->getMessage(), $e->getCode(), $e);
            }

            try {
                $signInResult = $this->signInAsUser($user);
            } catch (Throwable $e) {
                throw new FailedToSendActionLink($e->getMessage(), $e->getCode(), $e);
            }

            if (!($idToken = $signInResult->idToken())) {
                // @codeCoverageIgnoreStart
                // This only happens if the response on Google's side has changed
                // If it does, the tests will fail, but we don't have to cover that
                throw new FailedToSendActionLink("Failed to send action link: Unable to retrieve ID token for user assigned to email {$email}");
                // @codeCoverageIgnoreEnd
            }

            $sendAction = $sendAction->withIdTokenString($idToken);
        }

        (new SendActionLink\GuzzleApiClientHandler($this->httpClient, $this->projectId))->handle($sendAction);
    }

    public function getEmailVerificationLink($email, $actionCodeSettings = null, ?string $locale = null): string
    {
        return $this->getEmailActionLink('VERIFY_EMAIL', $email, $actionCodeSettings, $locale);
    }

    public function sendEmailVerificationLink($email, $actionCodeSettings = null, ?string $locale = null): void
    {
        $this->sendEmailActionLink('VERIFY_EMAIL', $email, $actionCodeSettings, $locale);
    }

    public function getPasswordResetLink($email, $actionCodeSettings = null, ?string $locale = null): string
    {
        return $this->getEmailActionLink('PASSWORD_RESET', $email, $actionCodeSettings, $locale);
    }

    public function sendPasswordResetLink($email, $actionCodeSettings = null, ?string $locale = null): void
    {
        $this->sendEmailActionLink('PASSWORD_RESET', $email, $actionCodeSettings, $locale);
    }

    public function getSignInWithEmailLink($email, $actionCodeSettings = null, ?string $locale = null): string
    {
        return $this->getEmailActionLink('EMAIL_SIGNIN', $email, $actionCodeSettings, $locale);
    }

    public function sendSignInWithEmailLink($email, $actionCodeSettings = null, ?string $locale = null): void
    {
        $this->sendEmailActionLink('EMAIL_SIGNIN', $email, $actionCodeSettings, $locale);
    }

    public function setCustomUserClaims($uid, ?array $claims): void
    {
        $uid = (string) (new Uid((string) $uid));
        $claims ??= [];

        $this->client->setCustomUserClaims($uid, $claims);
    }

    public function createCustomToken($uid, array $claims = [], $ttl = 3600): UnencryptedToken
    {
        $uid = (string) (new Uid((string) $uid));

        $generator = $this->tokenGenerator;

        if ($generator instanceof CustomTokenGenerator) {
            $tokenString = $generator->createCustomToken($uid, $claims, $ttl)->toString();
        } elseif ($generator instanceof CustomTokenViaGoogleIam) {
            $expiresAt = $this->clock->now()->add(Duration::make($ttl)->value());

            $tokenString = $generator->createCustomToken($uid, $claims, $expiresAt)->toString();
        } else {
            throw new AuthError('Custom Token Generation is disabled because the current credentials do not permit it');
        }

        return $this->parseToken($tokenString);
    }

    public function parseToken(string $tokenString): UnencryptedToken
    {
        try {
            $parsedToken = Configuration::forUnsecuredSigner()->parser()->parse($tokenString);
            \assert($parsedToken instanceof UnencryptedToken);
        } catch (Throwable $e) {
            throw new InvalidArgumentException('The given token could not be parsed: '.$e->getMessage());
        }

        return $parsedToken;
    }

    public function verifyIdToken($idToken, bool $checkIfRevoked = false, int $leewayInSeconds = null): UnencryptedToken
    {
        $verifier = $this->idTokenVerifier;

        $idTokenString = \is_string($idToken) ? $idToken : $idToken->toString();

        try {
            if ($leewayInSeconds !== null) {
                $verifier->verifyIdTokenWithLeeway($idTokenString, $leewayInSeconds);
            } else {
                $verifier->verifyIdToken($idTokenString);
            }
        } catch (Throwable $e) {
            throw new FailedToVerifyToken($e->getMessage());
        }

        $verifiedToken = $this->parseToken($idTokenString);

        if (!$checkIfRevoked) {
            return $verifiedToken;
        }

        try {
            $user = $this->getUser($verifiedToken->claims()->get('sub'));
        } catch (Throwable $e) {
            throw new FailedToVerifyToken("Error while getting the token's user: {$e->getMessage()}", 0, $e);
        }

        if ($this->userSessionHasBeenRevoked($verifiedToken, $user, $leewayInSeconds)) {
            throw new RevokedIdToken($verifiedToken);
        }

        return $verifiedToken;
    }

    public function verifySessionCookie(string $sessionCookie, bool $checkIfRevoked = false, ?int $leewayInSeconds = null): UnencryptedToken
    {
        $verifier = $this->sessionCookieVerifier;

        try {
            if ($leewayInSeconds !== null) {
                $verifier->verifySessionCookieWithLeeway($sessionCookie, $leewayInSeconds);
            } else {
                $verifier->verifySessionCookie($sessionCookie);
            }
        } catch (Throwable $e) {
            throw new FailedToVerifySessionCookie($e->getMessage());
        }

        $verifiedSessionCookie = $this->parseToken($sessionCookie);

        if (!$checkIfRevoked) {
            return $verifiedSessionCookie;
        }

        try {
            $user = $this->getUser($verifiedSessionCookie->claims()->get('sub'));
        } catch (Throwable $e) {
            throw new FailedToVerifySessionCookie("Error while getting the session cookie's user: {$e->getMessage()}", 0, $e);
        }

        if ($this->userSessionHasBeenRevoked($verifiedSessionCookie, $user, $leewayInSeconds)) {
            throw new RevokedSessionCookie($verifiedSessionCookie);
        }

        return $verifiedSessionCookie;
    }

    public function verifyPasswordResetCode(string $oobCode): string
    {
        $response = $this->client->verifyPasswordResetCode($oobCode);

        return JSON::decode((string) $response->getBody(), true)['email'];
    }

    public function confirmPasswordReset(string $oobCode, $newPassword, bool $invalidatePreviousSessions = true): string
    {
        $newPassword = (string) (new ClearTextPassword((string) $newPassword));

        $response = $this->client->confirmPasswordReset($oobCode, (string) $newPassword);

        $email = JSON::decode((string) $response->getBody(), true)['email'];

        if ($invalidatePreviousSessions) {
            $this->revokeRefreshTokens($this->getUserByEmail($email)->uid);
        }

        return $email;
    }

    public function revokeRefreshTokens($uid): void
    {
        $uid = (string) (new Uid((string) $uid));

        $this->client->revokeRefreshTokens($uid);
    }

    public function unlinkProvider($uid, $provider): UserRecord
    {
        $uid = (string) (new Uid((string) $uid));
        $provider = \array_map('strval', (array) $provider);

        $response = $this->client->unlinkProvider($uid, $provider);

        return $this->getUserRecordFromResponse($response);
    }

    public function signInAsUser($user, ?array $claims = null): SignInResult
    {
        $claims ??= [];
        $uid = $user instanceof UserRecord ? $user->uid : (string) $user;

        try {
            $customToken = $this->createCustomToken($uid, $claims);
        } catch (Throwable $e) {
            throw FailedToSignIn::fromPrevious($e);
        }

        $action = SignInWithCustomToken::fromValue($customToken->toString());

        if ($this->tenantId !== null) {
            $action = $action->withTenantId($this->tenantId);
        }

        return $this->signInHandler->handle($action);
    }

    public function signInWithCustomToken($token): SignInResult
    {
        $token = $token instanceof Token ? $token->toString() : $token;

        $action = SignInWithCustomToken::fromValue($token);

        if ($this->tenantId !== null) {
            $action = $action->withTenantId($this->tenantId);
        }

        return $this->signInHandler->handle($action);
    }

    public function signInWithRefreshToken(string $refreshToken): SignInResult
    {
        $action = SignInWithRefreshToken::fromValue($refreshToken);

        if ($this->tenantId !== null) {
            $action = $action->withTenantId($this->tenantId);
        }

        return $this->signInHandler->handle($action);
    }

    public function signInWithEmailAndPassword($email, $clearTextPassword): SignInResult
    {
        $email = (string) (new Email((string) $email));
        $clearTextPassword = (string) (new ClearTextPassword((string) $clearTextPassword));

        $action = SignInWithEmailAndPassword::fromValues($email, $clearTextPassword);

        if ($this->tenantId !== null) {
            $action = $action->withTenantId($this->tenantId);
        }

        return $this->signInHandler->handle($action);
    }

    public function signInWithEmailAndOobCode($email, string $oobCode): SignInResult
    {
        $email = (string) (new Email((string) $email));

        $action = SignInWithEmailAndOobCode::fromValues($email, $oobCode);

        if ($this->tenantId !== null) {
            $action = $action->withTenantId($this->tenantId);
        }

        return $this->signInHandler->handle($action);
    }

    public function signInAnonymously(): SignInResult
    {
        $action = SignInAnonymously::new();

        if ($this->tenantId !== null) {
            $action = $action->withTenantId($this->tenantId);
        }

        $result = $this->signInHandler->handle($action);

        if ($result->idToken()) {
            return $result;
        }

        if ($uid = ($result->data()['localId'] ?? null)) {
            return $this->signInAsUser($uid);
        }

        throw new FailedToSignIn('Failed to sign in anonymously: No ID token or UID available');
    }

    public function signInWithIdpAccessToken($provider, string $accessToken, $redirectUrl = null, ?string $oauthTokenSecret = null, ?string $linkingIdToken = null, ?string $rawNonce = null): SignInResult
    {
        $provider = (string) $provider;
        $redirectUrl = \trim((string) ($redirectUrl ?? 'http://localhost'));
        $linkingIdToken = \trim((string) $linkingIdToken);
        $oauthTokenSecret = \trim((string) $oauthTokenSecret);
        $rawNonce = \trim((string) $rawNonce);

        if ($oauthTokenSecret !== '') {
            $action = SignInWithIdpCredentials::withAccessTokenAndOauthTokenSecret($provider, $accessToken, $oauthTokenSecret);
        } else {
            $action = SignInWithIdpCredentials::withAccessToken($provider, $accessToken);
        }

        if ($linkingIdToken !== '') {
            $action = $action->withLinkingIdToken($linkingIdToken);
        }

        if ($rawNonce !== '') {
            $action = $action->withRawNonce($rawNonce);
        }

        if ($redirectUrl !== '') {
            $action = $action->withRequestUri($redirectUrl);
        }

        if ($this->tenantId !== null) {
            $action = $action->withTenantId($this->tenantId);
        }

        return $this->signInHandler->handle($action);
    }

    public function signInWithIdpIdToken($provider, $idToken, $redirectUrl = null, ?string $linkingIdToken = null, ?string $rawNonce = null): SignInResult
    {
        $provider = \trim((string) $provider);
        $redirectUrl = \trim((string) ($redirectUrl ?? 'http://localhost'));
        $linkingIdToken = \trim((string) $linkingIdToken);
        $rawNonce = \trim((string) $rawNonce);

        if ($idToken instanceof Token) {
            $idToken = $idToken->toString();
        }

        $action = SignInWithIdpCredentials::withIdToken($provider, $idToken);

        if ($rawNonce !== '') {
            $action = $action->withRawNonce($rawNonce);
        }

        if ($linkingIdToken !== '') {
            $action = $action->withLinkingIdToken($linkingIdToken);
        }

        if ($redirectUrl !== '') {
            $action = $action->withRequestUri($redirectUrl);
        }

        if ($this->tenantId !== null) {
            $action = $action->withTenantId($this->tenantId);
        }

        return $this->signInHandler->handle($action);
    }

    public function createSessionCookie($idToken, $ttl): string
    {
        return (new CreateSessionCookie\GuzzleApiClientHandler($this->httpClient, $this->projectId))
            ->handle(CreateSessionCookie::forIdToken($idToken, $this->tenantId, $ttl, $this->clock))
        ;
    }

    /**
     * Gets the user ID from the response and queries a full UserRecord object for it.
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    private function getUserRecordFromResponse(ResponseInterface $response): UserRecord
    {
        $uid = JSON::decode((string) $response->getBody(), true)['localId'];

        return $this->getUser($uid);
    }

    private function userSessionHasBeenRevoked(UnencryptedToken $verifiedToken, UserRecord $user, ?int $leewayInSeconds = null): bool
    {
        // The timestamp, in seconds, which marks a boundary, before which Firebase ID token are considered revoked.
        $validSince = $user->tokensValidAfterTime ?? null;

        if (!($validSince instanceof \DateTimeImmutable)) {
            // The user hasn't logged in yet, so there's nothing to revoke
            return false;
        }

        $tokenAuthenticatedAt = DT::toUTCDateTimeImmutable($verifiedToken->claims()->get('auth_time'));

        if ($leewayInSeconds) {
            $tokenAuthenticatedAt = $tokenAuthenticatedAt->modify('-'.$leewayInSeconds.' seconds');
        }

        return $tokenAuthenticatedAt->getTimestamp() < $validSince->getTimestamp();
    }
}

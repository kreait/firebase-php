<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use Firebase\Auth\Token\Domain\Generator as TokenGenerator;
use Firebase\Auth\Token\Domain\Verifier;
use Firebase\Auth\Token\Exception\InvalidToken;
use GuzzleHttp\ClientInterface;
use Kreait\Firebase\Auth\ActionCodeSettings;
use Kreait\Firebase\Auth\ActionCodeSettings\ValidatedActionCodeSettings;
use Kreait\Firebase\Auth\ApiClient;
use Kreait\Firebase\Auth\CreateActionLink;
use Kreait\Firebase\Auth\CreateSessionCookie;
use Kreait\Firebase\Auth\DeleteUsersRequest;
use Kreait\Firebase\Auth\DeleteUsersResult;
use Kreait\Firebase\Auth\IdTokenVerifier;
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
use Kreait\Firebase\Exception\Auth\RevokedIdToken;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Util\DT;
use Kreait\Firebase\Util\JSON;
use Kreait\Firebase\Value\ClearTextPassword;
use Kreait\Firebase\Value\Email;
use Kreait\Firebase\Value\Uid;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token;
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
    private TokenGenerator $tokenGenerator;
    private Verifier $idTokenVerifier;
    private SignInHandler $signInHandler;
    private ?string $tenantId;
    private string $projectId;

    public function __construct(
        ApiClient $client,
        ClientInterface $httpClient,
        TokenGenerator $tokenGenerator,
        Verifier $idTokenVerifier,
        SignInHandler $signInHandler,
        string $projectId,
        ?string $tenantId = null
    ) {
        $this->client = $client;
        $this->httpClient = $httpClient;
        $this->tokenGenerator = $tokenGenerator;
        $this->idTokenVerifier = $idTokenVerifier;
        $this->signInHandler = $signInHandler;
        $this->tenantId = $tenantId;
        $this->projectId = $projectId;
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

        return (new CreateActionLink\GuzzleApiClientHandler($this->httpClient))
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

        (new SendActionLink\GuzzleApiClientHandler($this->httpClient))->handle($sendAction);
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

    public function createCustomToken($uid, array $claims = []): Token
    {
        $uid = (string) (new Uid((string) $uid));

        return $this->tokenGenerator->createCustomToken($uid, $claims);
    }

    public function parseToken(string $tokenString): Token
    {
        try {
            return Configuration::forUnsecuredSigner()->parser()->parse($tokenString);
        } catch (Throwable $e) {
            throw new InvalidArgumentException('The given token could not be parsed: '.$e->getMessage());
        }
    }

    public function verifyIdToken($idToken, bool $checkIfRevoked = false): Token
    {
        $leewayInSeconds = 300;
        $verifier = $this->idTokenVerifier;

        if ($verifier instanceof IdTokenVerifier) {
            $verifier = $verifier->withLeewayInSeconds($leewayInSeconds);
        }

        $verifiedToken = $verifier->verifyIdToken($idToken);

        if ($checkIfRevoked) {
            // @codeCoverageIgnoreStart
            if (!($verifiedToken instanceof Token\Plain)) {
                throw new InvalidToken($verifiedToken, 'The ID token could not be decrypted');
            }
            // @codeCoverageIgnoreEnd

            try {
                $user = $this->getUser($verifiedToken->claims()->get('sub'));
            } catch (Throwable $e) {
                throw new InvalidToken($verifiedToken, "Error while getting the token's user: {$e->getMessage()}", $e->getCode(), $e);
            }

            // The timestamp, in seconds, which marks a boundary, before which Firebase ID token are considered revoked.
            $validSince = $user->tokensValidAfterTime ?? null;

            if (!($validSince instanceof \DateTimeImmutable)) {
                return $verifiedToken;
            }

            $tokenAuthenticatedAt = DT::toUTCDateTimeImmutable($verifiedToken->claims()->get('auth_time'));
            $tokenAuthenticatedAtWithLeeway = $tokenAuthenticatedAt->modify('-'.$leewayInSeconds.' seconds');

            $validSinceWithLeeway = DT::toUTCDateTimeImmutable($validSince)->modify('-'.$leewayInSeconds.' seconds');

            if ($tokenAuthenticatedAtWithLeeway->getTimestamp() < $validSinceWithLeeway->getTimestamp()) {
                throw new RevokedIdToken($verifiedToken);
            }
        }

        return $verifiedToken;
    }

    public function verifyPasswordResetCode(string $oobCode): void
    {
        // Not returning the email on purpose to not break BC
        $this->verifyPasswordResetCodeAndReturnEmail($oobCode);
    }

    public function verifyPasswordResetCodeAndReturnEmail(string $oobCode): string
    {
        $response = $this->client->verifyPasswordResetCode($oobCode);

        return JSON::decode((string) $response->getBody(), true)['email'];
    }

    public function confirmPasswordReset(string $oobCode, $newPassword, bool $invalidatePreviousSessions = true): void
    {
        // Not returning the email on purpose to not break BC
        $this->confirmPasswordResetAndReturnEmail($oobCode, $newPassword, $invalidatePreviousSessions);
    }

    public function confirmPasswordResetAndReturnEmail(string $oobCode, $newPassword, bool $invalidatePreviousSessions = true): string
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

        $customToken = $this->createCustomToken($uid, $claims);

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

    /**
     * @deprecated 5.26.0 Use {@see signInWithIdpAccessToken()} with 'twitter.com' instead.
     * @codeCoverageIgnore
     */
    public function signInWithTwitterOauthCredential(string $accessToken, string $oauthTokenSecret, ?string $redirectUrl = null, ?string $linkingIdToken = null): SignInResult
    {
        return $this->signInWithIdpAccessToken('twitter.com', $accessToken, $redirectUrl, $oauthTokenSecret, $linkingIdToken);
    }

    /**
     * @deprecated 5.26.0 Use {@see signInWithIdpIdToken()} with 'google.com' instead.
     * @codeCoverageIgnore
     */
    public function signInWithGoogleIdToken(string $idToken, ?string $redirectUrl = null, ?string $linkingIdToken = null): SignInResult
    {
        return $this->signInWithIdpIdToken('google.com', $idToken, $redirectUrl, $linkingIdToken);
    }

    /**
     * @deprecated 5.26.0 Use {@see signInWithIdpAccessToken()} with 'facebook.com' instead.
     * @codeCoverageIgnore
     */
    public function signInWithFacebookAccessToken(string $accessToken, ?string $redirectUrl = null, ?string $linkingIdToken = null): SignInResult
    {
        return $this->signInWithIdpAccessToken('facebook.com', $accessToken, $redirectUrl, null, $linkingIdToken);
    }

    /**
     * @deprecated 5.26.0 Use {@see signInWithIdpIdToken()} with 'apple.com' instead.
     * @codeCoverageIgnore
     */
    public function signInWithAppleIdToken(string $idToken, ?string $rawNonce = null, ?string $redirectUrl = null, ?string $linkingIdToken = null): SignInResult
    {
        return $this->signInWithIdpIdToken('apple.com', $idToken, $redirectUrl, $linkingIdToken, $rawNonce);
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
        return (new CreateSessionCookie\GuzzleApiClientHandler($this->httpClient))
            ->handle(CreateSessionCookie::forIdToken($idToken, $ttl))
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
}

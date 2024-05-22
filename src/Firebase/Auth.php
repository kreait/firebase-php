<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use Beste\Json;
use DateInterval;
use DateTimeImmutable;
use Kreait\Firebase\Auth\ActionCodeSettings;
use Kreait\Firebase\Auth\ActionCodeSettings\ValidatedActionCodeSettings;
use Kreait\Firebase\Auth\ApiClient;
use Kreait\Firebase\Auth\CustomTokenViaGoogleCredentials;
use Kreait\Firebase\Auth\DeleteUsersRequest;
use Kreait\Firebase\Auth\DeleteUsersResult;
use Kreait\Firebase\Auth\SendActionLink\FailedToSendActionLink;
use Kreait\Firebase\Auth\SignIn\FailedToSignIn;
use Kreait\Firebase\Auth\SignInAnonymously;
use Kreait\Firebase\Auth\SignInResult;
use Kreait\Firebase\Auth\SignInWithCustomToken;
use Kreait\Firebase\Auth\SignInWithEmailAndOobCode;
use Kreait\Firebase\Auth\SignInWithEmailAndPassword;
use Kreait\Firebase\Auth\SignInWithIdpCredentials;
use Kreait\Firebase\Auth\SignInWithRefreshToken;
use Kreait\Firebase\Auth\UserQuery;
use Kreait\Firebase\Auth\UserRecord;
use Kreait\Firebase\Exception\Auth\AuthError;
use Kreait\Firebase\Exception\Auth\FailedToVerifySessionCookie;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Kreait\Firebase\Exception\Auth\RevokedIdToken;
use Kreait\Firebase\Exception\Auth\RevokedSessionCookie;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\JWT\IdTokenVerifier;
use Kreait\Firebase\JWT\SessionCookieVerifier;
use Kreait\Firebase\JWT\Token\Parser;
use Kreait\Firebase\Request\CreateUser;
use Kreait\Firebase\Request\UpdateUser;
use Kreait\Firebase\Util\DT;
use Kreait\Firebase\Value\ClearTextPassword;
use Kreait\Firebase\Value\Email;
use Kreait\Firebase\Value\Uid;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\UnencryptedToken;
use Psr\Clock\ClockInterface;
use Psr\Http\Message\ResponseInterface;
use Stringable;
use Throwable;
use Traversable;

use function array_fill_keys;
use function array_map;
use function assert;
use function is_string;
use function mb_strtolower;
use function trim;

/**
 * @internal
 */
final class Auth implements Contract\Auth
{
    private readonly Parser $jwtParser;

    public function __construct(
        private readonly ApiClient $client,
        private readonly ?CustomTokenViaGoogleCredentials $tokenGenerator,
        private readonly IdTokenVerifier $idTokenVerifier,
        private readonly SessionCookieVerifier $sessionCookieVerifier,
        private readonly ClockInterface $clock,
    ) {
        $this->jwtParser = new Parser(new JoseEncoder());
    }

    public function getUser(Stringable|string $uid): UserRecord
    {
        $uid = Uid::fromString($uid)->value;

        $userRecord = $this->getUsers([$uid])[$uid] ?? null;

        if ($userRecord !== null) {
            return $userRecord;
        }

        throw new UserNotFound("No user with uid '{$uid}' found.");
    }

    public function getUsers(array $uids): array
    {
        $uids = array_map(static fn($uid) => Uid::fromString($uid)->value, $uids);

        $users = array_fill_keys($uids, null);

        $response = $this->client->getAccountInfo($uids);

        $data = Json::decode((string) $response->getBody(), true);

        foreach ($data['users'] ?? [] as $userData) {
            $userRecord = UserRecord::fromResponseData($userData);
            $users[$userRecord->uid] = $userRecord;
        }

        return $users;
    }

    public function queryUsers(UserQuery|array $query): array
    {
        $userQuery = $query instanceof UserQuery ? $query : UserQuery::fromArray($query);

        $response = $this->client->queryUsers($userQuery);

        $data = Json::decode((string) $response->getBody(), true);

        $users = [];

        foreach ($data['userInfo'] ?? [] as $userData) {
            $userRecord = UserRecord::fromResponseData($userData);
            $users[$userRecord->uid] = $userRecord;
        }

        return $users;
    }

    public function listUsers(int $maxResults = 1000, int $batchSize = 1000): Traversable
    {
        $pageToken = null;
        $count = 0;

        if ($batchSize > $maxResults) {
            $batchSize = $maxResults;
        }

        do {
            $response = $this->client->downloadAccount($batchSize, $pageToken);
            $result = Json::decode((string) $response->getBody(), true);

            foreach ((array) ($result['users'] ?? []) as $userData) {
                yield UserRecord::fromResponseData($userData);

                if (++$count === $maxResults) {
                    return;
                }
            }

            $pageToken = $result['nextPageToken'] ?? null;
        } while ($pageToken);
    }

    public function createUser(array|CreateUser $properties): UserRecord
    {
        $request = $properties instanceof CreateUser
            ? $properties
            : CreateUser::withProperties($properties);

        $response = $this->client->createUser($request);

        return $this->getUserRecordFromResponse($response);
    }

    public function updateUser(Stringable|string $uid, array|UpdateUser $properties): UserRecord
    {
        $request = $properties instanceof UpdateUser
            ? $properties
            : UpdateUser::withProperties($properties);

        $request = $request->withUid($uid);

        $response = $this->client->updateUser($request);

        return $this->getUserRecordFromResponse($response);
    }

    public function createUserWithEmailAndPassword(Stringable|string $email, Stringable|string $password): UserRecord
    {
        return $this->createUser(
            CreateUser::new()
                ->withUnverifiedEmail($email)
                ->withClearTextPassword($password),
        );
    }

    public function getUserByEmail(Stringable|string $email): UserRecord
    {
        $email = Email::fromString((string) $email)->value;

        $response = $this->client->getUserByEmail($email);

        $data = Json::decode((string) $response->getBody(), true);

        if (empty($data['users'][0])) {
            throw new UserNotFound("No user with email '{$email}' found.");
        }

        return UserRecord::fromResponseData($data['users'][0]);
    }

    public function getUserByPhoneNumber(Stringable|string $phoneNumber): UserRecord
    {
        $phoneNumber = (string) $phoneNumber;

        $response = $this->client->getUserByPhoneNumber($phoneNumber);

        $data = Json::decode((string) $response->getBody(), true);

        if (empty($data['users'][0])) {
            throw new UserNotFound("No user with phone number '{$phoneNumber}' found.");
        }

        return UserRecord::fromResponseData($data['users'][0]);
    }

    public function createAnonymousUser(): UserRecord
    {
        return $this->createUser(CreateUser::new());
    }

    public function changeUserPassword(Stringable|string $uid, Stringable|string $newPassword): UserRecord
    {
        return $this->updateUser($uid, UpdateUser::new()->withClearTextPassword($newPassword));
    }

    public function changeUserEmail(Stringable|string $uid, Stringable|string $newEmail): UserRecord
    {
        return $this->updateUser($uid, UpdateUser::new()->withEmail($newEmail));
    }

    public function enableUser(Stringable|string $uid): UserRecord
    {
        return $this->updateUser($uid, UpdateUser::new()->markAsEnabled());
    }

    public function disableUser(Stringable|string $uid): UserRecord
    {
        return $this->updateUser($uid, UpdateUser::new()->markAsDisabled());
    }

    public function deleteUser(Stringable|string $uid): void
    {
        $uid = Uid::fromString($uid)->value;

        try {
            $this->client->deleteUser($uid);
        } catch (UserNotFound) {
            throw new UserNotFound("No user with uid '{$uid}' found.");
        }
    }

    public function deleteUsers(iterable $uids, bool $forceDeleteEnabledUsers = false): DeleteUsersResult
    {
        $request = DeleteUsersRequest::withUids($uids, $forceDeleteEnabledUsers);

        $response = $this->client->deleteUsers(
            $request->uids(),
            $request->enabledUsersShouldBeForceDeleted(),
        );

        return DeleteUsersResult::fromRequestAndResponse($request, $response);
    }

    public function getEmailActionLink(string $type, Stringable|string $email, $actionCodeSettings = null, ?string $locale = null): string
    {
        $email = Email::fromString((string) $email)->value;

        if ($actionCodeSettings === null) {
            $actionCodeSettings = ValidatedActionCodeSettings::empty();
        } else {
            $actionCodeSettings = $actionCodeSettings instanceof ActionCodeSettings
                ? $actionCodeSettings
                : ValidatedActionCodeSettings::fromArray($actionCodeSettings);
        }

        return $this->client->getEmailActionLink($type, $email, $actionCodeSettings, $locale);
    }

    public function sendEmailActionLink(string $type, Stringable|string $email, $actionCodeSettings = null, ?string $locale = null): void
    {
        $email = Email::fromString((string) $email)->value;

        if ($actionCodeSettings === null) {
            $actionCodeSettings = ValidatedActionCodeSettings::empty();
        } else {
            $actionCodeSettings = $actionCodeSettings instanceof ActionCodeSettings
                ? $actionCodeSettings
                : ValidatedActionCodeSettings::fromArray($actionCodeSettings);
        }

        $idToken = null;

        if (mb_strtolower($type) === 'verify_email') {
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
                throw new FailedToSendActionLink("Failed to send action link: Unable to retrieve ID token for user assigned to email {$email}");
            }
        }

        $this->client->sendEmailActionLink($type, $email, $actionCodeSettings, $locale, $idToken);
    }

    public function getEmailVerificationLink(Stringable|string $email, $actionCodeSettings = null, ?string $locale = null): string
    {
        return $this->getEmailActionLink('VERIFY_EMAIL', $email, $actionCodeSettings, $locale);
    }

    public function sendEmailVerificationLink(Stringable|string $email, $actionCodeSettings = null, ?string $locale = null): void
    {
        $this->sendEmailActionLink('VERIFY_EMAIL', $email, $actionCodeSettings, $locale);
    }

    public function getPasswordResetLink(Stringable|string $email, $actionCodeSettings = null, ?string $locale = null): string
    {
        return $this->getEmailActionLink('PASSWORD_RESET', $email, $actionCodeSettings, $locale);
    }

    public function sendPasswordResetLink(Stringable|string $email, $actionCodeSettings = null, ?string $locale = null): void
    {
        $this->sendEmailActionLink('PASSWORD_RESET', $email, $actionCodeSettings, $locale);
    }

    public function getSignInWithEmailLink(Stringable|string $email, $actionCodeSettings = null, ?string $locale = null): string
    {
        return $this->getEmailActionLink('EMAIL_SIGNIN', $email, $actionCodeSettings, $locale);
    }

    public function sendSignInWithEmailLink(Stringable|string $email, $actionCodeSettings = null, ?string $locale = null): void
    {
        $this->sendEmailActionLink('EMAIL_SIGNIN', $email, $actionCodeSettings, $locale);
    }

    public function setCustomUserClaims(Stringable|string $uid, ?array $claims): void
    {
        $uid = Uid::fromString($uid)->value;
        $claims ??= [];

        $this->client->setCustomUserClaims($uid, $claims);
    }

    public function createCustomToken(Stringable|string $uid, array $claims = [], $ttl = 3600): UnencryptedToken
    {
        if (!$this->tokenGenerator) {
            throw new AuthError('Custom Token Generation is disabled because the current credentials do not permit it');
        }

        $uid = Uid::fromString($uid)->value;

        if (!$ttl instanceof DateInterval) {
            $ttl = new DateInterval(sprintf('PT%sS', $ttl));
        }

        $expiresAt = $this->clock->now()->add($ttl);

        $token = $this->tokenGenerator->createCustomToken($uid, $claims, $expiresAt);

        assert($token instanceof UnencryptedToken);

        return $token;
    }

    public function parseToken(string $tokenString): UnencryptedToken
    {
        try {
            $parsedToken = $this->jwtParser->parse($tokenString);
            assert($parsedToken instanceof UnencryptedToken);
        } catch (Throwable $e) {
            throw new InvalidArgumentException('The given token could not be parsed: '.$e->getMessage());
        }

        return $parsedToken;
    }

    public function verifyIdToken($idToken, bool $checkIfRevoked = false, ?int $leewayInSeconds = null): UnencryptedToken
    {
        $verifier = $this->idTokenVerifier;

        $idTokenString = is_string($idToken) ? $idToken : $idToken->toString();
        // The ID Token is annotated as non-empty-string or a valid Token, so it cannot be empty
        // Static analysis are not always sure about that, so we'll help them here.
        // The assertion is necessary for lcobucci/jwt 4.* but not needed for 5.*
        // @phpstan-ignore function.alreadyNarrowedType, notIdentical.alwaysTrue
        assert($idTokenString !== '');

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

        return Json::decode((string) $response->getBody(), true)['email'];
    }

    public function confirmPasswordReset(string $oobCode, $newPassword, bool $invalidatePreviousSessions = true): string
    {
        $newPassword = ClearTextPassword::fromString($newPassword)->value;

        $response = $this->client->confirmPasswordReset($oobCode, $newPassword);

        $email = Json::decode((string) $response->getBody(), true)['email'];

        if ($invalidatePreviousSessions) {
            $this->revokeRefreshTokens($this->getUserByEmail($email)->uid);
        }

        return $email;
    }

    public function revokeRefreshTokens(Stringable|string $uid): void
    {
        $uid = Uid::fromString($uid)->value;

        $this->client->revokeRefreshTokens($uid);
    }

    public function unlinkProvider($uid, $provider): UserRecord
    {
        $uid = Uid::fromString($uid)->value;

        $provider = array_values(
            array_filter(
                array_map('strval', (array) $provider),
                static fn(string $value) => $value !== '',
            ),
        );

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

        return $this->client->handleSignIn(SignInWithCustomToken::fromValue($customToken->toString()));
    }

    public function signInWithCustomToken($token): SignInResult
    {
        $token = $token instanceof Token ? $token->toString() : $token;

        $action = SignInWithCustomToken::fromValue($token);

        return $this->client->handleSignIn($action);
    }

    public function signInWithRefreshToken(string $refreshToken): SignInResult
    {
        return $this->client->handleSignIn(SignInWithRefreshToken::fromValue($refreshToken));
    }

    public function signInWithEmailAndPassword($email, $clearTextPassword): SignInResult
    {
        $email = Email::fromString((string) $email)->value;
        $clearTextPassword = ClearTextPassword::fromString($clearTextPassword)->value;

        return $this->client->handleSignIn(SignInWithEmailAndPassword::fromValues($email, $clearTextPassword));
    }

    public function signInWithEmailAndOobCode($email, string $oobCode): SignInResult
    {
        $email = Email::fromString((string) $email)->value;

        return $this->client->handleSignIn(SignInWithEmailAndOobCode::fromValues($email, $oobCode));
    }

    public function signInAnonymously(): SignInResult
    {
        $result = $this->client->handleSignIn(SignInAnonymously::new());

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
        $redirectUrl = trim((string) ($redirectUrl ?? 'http://localhost'));
        $linkingIdToken = trim((string) $linkingIdToken);
        $oauthTokenSecret = trim((string) $oauthTokenSecret);
        $rawNonce = trim((string) $rawNonce);

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

        return $this->client->handleSignIn($action);
    }

    public function signInWithIdpIdToken($provider, $idToken, $redirectUrl = null, ?string $linkingIdToken = null, ?string $rawNonce = null): SignInResult
    {
        $provider = trim((string) $provider);
        $redirectUrl = trim((string) ($redirectUrl ?? 'http://localhost'));
        $linkingIdToken = trim((string) $linkingIdToken);
        $rawNonce = trim((string) $rawNonce);

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

        return $this->client->handleSignIn($action);
    }

    public function createSessionCookie($idToken, $ttl): string
    {
        if ($idToken instanceof Token) {
            $idToken = $idToken->toString();
        }

        return $this->client->createSessionCookie($idToken, $ttl);
    }

    /**
     * Gets the user ID from the response and queries a full UserRecord object for it.
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    private function getUserRecordFromResponse(ResponseInterface $response): UserRecord
    {
        $uid = Json::decode((string) $response->getBody(), true)['localId'];

        return $this->getUser($uid);
    }

    private function userSessionHasBeenRevoked(UnencryptedToken $verifiedToken, UserRecord $user, ?int $leewayInSeconds = null): bool
    {
        // The timestamp, in seconds, which marks a boundary, before which Firebase ID token are considered revoked.
        $validSince = $user->tokensValidAfterTime ?? null;

        if (!$validSince instanceof DateTimeImmutable) {
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

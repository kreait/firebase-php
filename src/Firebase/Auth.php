<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use Firebase\Auth\Token\Domain\Generator as TokenGenerator;
use Firebase\Auth\Token\Domain\Verifier;
use Firebase\Auth\Token\Exception\ExpiredToken;
use Firebase\Auth\Token\Exception\InvalidSignature;
use Firebase\Auth\Token\Exception\InvalidToken;
use Firebase\Auth\Token\Exception\IssuedInTheFuture;
use Firebase\Auth\Token\Exception\UnknownKey;
use Generator;
use Kreait\Clock;
use Kreait\Firebase\Auth\ActionCodeSettings;
use Kreait\Firebase\Auth\ActionCodeSettings\ValidatedActionCodeSettings;
use Kreait\Firebase\Auth\ApiClient;
use Kreait\Firebase\Auth\CreateActionLink;
use Kreait\Firebase\Auth\CreateActionLink\FailedToCreateActionLink;
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
use Kreait\Firebase\Exception\Auth\ExpiredOobCode;
use Kreait\Firebase\Exception\Auth\InvalidOobCode;
use Kreait\Firebase\Exception\Auth\OperationNotAllowed;
use Kreait\Firebase\Exception\Auth\RevokedIdToken;
use Kreait\Firebase\Exception\Auth\UserDisabled;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Util\DT;
use Kreait\Firebase\Util\JSON;
use Kreait\Firebase\Value\ClearTextPassword;
use Kreait\Firebase\Value\Email;
use Kreait\Firebase\Value\PhoneNumber;
use Kreait\Firebase\Value\Provider;
use Kreait\Firebase\Value\Uid;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Token;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Throwable;

class Auth
{
    /** @var ApiClient */
    private $client;

    /** @var TokenGenerator */
    private $tokenGenerator;

    /** @var Verifier */
    private $idTokenVerifier;

    /** @var SignInHandler */
    private $signInHandler;

    /**
     * @param array<int, ApiClient|TokenGenerator|Verifier|Clock|SignInHandler> $x
     *
     * @internal
     */
    public function __construct(...$x)
    {
        foreach ($x as $arg) {
            if ($arg instanceof ApiClient) {
                $this->client = $arg;
            } elseif ($arg instanceof TokenGenerator) {
                $this->tokenGenerator = $arg;
            } elseif ($arg instanceof Verifier) {
                $this->idTokenVerifier = $arg;
            } elseif ($arg instanceof SignInHandler) {
                $this->signInHandler = $arg;
            }
        }
    }

    /**
     * @param Uid|string $uid
     *
     * @throws UserNotFound
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function getUser($uid): UserRecord
    {
        $uid = $uid instanceof Uid ? $uid : new Uid($uid);

        $response = $this->client->getAccountInfo((string) $uid);

        $data = JSON::decode((string) $response->getBody(), true);

        if (empty($data['users'][0])) {
            throw new UserNotFound("No user with uid '{$uid}' found.");
        }

        return UserRecord::fromResponseData($data['users'][0]);
    }

    /**
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     *
     * @return Generator|UserRecord[]
     */
    public function listUsers(int $maxResults = 1000, int $batchSize = 1000): Generator
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

    /**
     * Creates a new user with the provided properties.
     *
     * @param array|Request\CreateUser $properties
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function createUser($properties): UserRecord
    {
        $request = $properties instanceof Request\CreateUser
            ? $properties
            : Request\CreateUser::withProperties($properties);

        $response = $this->client->createUser($request);

        return $this->getUserRecordFromResponse($response);
    }

    /**
     * Updates the given user with the given properties.
     *
     * @param Uid|string $uid
     * @param array|Request\UpdateUser $properties
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function updateUser($uid, $properties): UserRecord
    {
        $request = $properties instanceof Request\UpdateUser
            ? $properties
            : Request\UpdateUser::withProperties($properties);

        $request = $request->withUid($uid);

        $response = $this->client->updateUser($request);

        return $this->getUserRecordFromResponse($response);
    }

    /**
     * @param Email|string $email
     * @param ClearTextPassword|string $password
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function createUserWithEmailAndPassword($email, $password): UserRecord
    {
        return $this->createUser(Request\CreateUser::new()
            ->withUnverifiedEmail($email)
            ->withClearTextPassword($password)
        );
    }

    /**
     * @param Email|string $email
     *
     * @throws UserNotFound
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function getUserByEmail($email): UserRecord
    {
        $email = $email instanceof Email ? $email : new Email($email);

        $response = $this->client->getUserByEmail((string) $email);

        $data = JSON::decode((string) $response->getBody(), true);

        if (empty($data['users'][0])) {
            throw new UserNotFound("No user with email '{$email}' found.");
        }

        return UserRecord::fromResponseData($data['users'][0]);
    }

    /**
     * @param PhoneNumber|string $phoneNumber
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function getUserByPhoneNumber($phoneNumber): UserRecord
    {
        $phoneNumber = $phoneNumber instanceof PhoneNumber ? $phoneNumber : new PhoneNumber($phoneNumber);

        $response = $this->client->getUserByPhoneNumber((string) $phoneNumber);

        $data = JSON::decode((string) $response->getBody(), true);

        if (empty($data['users'][0])) {
            throw new UserNotFound("No user with phone number '{$phoneNumber}' found.");
        }

        return UserRecord::fromResponseData($data['users'][0]);
    }

    /**
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function createAnonymousUser(): UserRecord
    {
        return $this->createUser(Request\CreateUser::new());
    }

    /**
     * @param Uid|string $uid
     * @param ClearTextPassword|string $newPassword
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function changeUserPassword($uid, $newPassword): UserRecord
    {
        return $this->updateUser($uid, Request\UpdateUser::new()->withClearTextPassword($newPassword));
    }

    /**
     * @param Uid|string $uid
     * @param Email|string $newEmail
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function changeUserEmail($uid, $newEmail): UserRecord
    {
        return $this->updateUser($uid, Request\UpdateUser::new()->withEmail($newEmail));
    }

    /**
     * @param Uid|string $uid
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function enableUser($uid): UserRecord
    {
        return $this->updateUser($uid, Request\UpdateUser::new()->markAsEnabled());
    }

    /**
     * @param Uid|string $uid
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function disableUser($uid): UserRecord
    {
        return $this->updateUser($uid, Request\UpdateUser::new()->markAsDisabled());
    }

    /**
     * @param Uid|string $uid
     *
     * @throws UserNotFound
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function deleteUser($uid): void
    {
        $uid = $uid instanceof Uid ? $uid : new Uid($uid);

        try {
            $this->client->deleteUser((string) $uid);
        } catch (UserNotFound $e) {
            throw new UserNotFound("No user with uid '{$uid}' found.");
        }
    }

    /**
     * @param Email|string $email
     * @param ActionCodeSettings|array|null $actionCodeSettings
     *
     * @throws FailedToCreateActionLink
     */
    public function getEmailActionLink(string $type, $email, $actionCodeSettings = null): string
    {
        $email = $email instanceof Email ? $email : new Email((string) $email);

        if ($actionCodeSettings === null) {
            $actionCodeSettings = ValidatedActionCodeSettings::empty();
        } else {
            $actionCodeSettings = $actionCodeSettings instanceof ActionCodeSettings
                ? $actionCodeSettings
                : ValidatedActionCodeSettings::fromArray($actionCodeSettings);
        }

        return (new CreateActionLink\GuzzleApiClientHandler($this->client))
            ->handle(CreateActionLink::new($type, $email, $actionCodeSettings));
    }

    /**
     * @param Email|string $email
     * @param ActionCodeSettings|array|null $actionCodeSettings
     *
     * @throws UserNotFound
     * @throws FailedToSendActionLink
     */
    public function sendEmailActionLink(string $type, $email, $actionCodeSettings = null, string $locale = null): void
    {
        $email = $email instanceof Email ? $email : new Email((string) $email);

        if ($actionCodeSettings === null) {
            $actionCodeSettings = ValidatedActionCodeSettings::empty();
        } else {
            $actionCodeSettings = $actionCodeSettings instanceof ActionCodeSettings
                ? $actionCodeSettings
                : ValidatedActionCodeSettings::fromArray($actionCodeSettings);
        }

        $createAction = CreateActionLink::new($type, $email, $actionCodeSettings);
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

        (new SendActionLink\GuzzleApiClientHandler($this->client))->handle($sendAction);
    }

    /**
     * @param Email|string $email
     * @param ActionCodeSettings|array|null $actionCodeSettings
     *
     * @throws FailedToCreateActionLink
     */
    public function getEmailVerificationLink($email, $actionCodeSettings = null): string
    {
        return $this->getEmailActionLink('VERIFY_EMAIL', $email, $actionCodeSettings);
    }

    /**
     * @param Email|string $email
     * @param ActionCodeSettings|array|null $actionCodeSettings
     *
     * @throws FailedToSendActionLink
     */
    public function sendEmailVerificationLink($email, $actionCodeSettings = null, string $locale = null): void
    {
        $this->sendEmailActionLink('VERIFY_EMAIL', $email, $actionCodeSettings, $locale);
    }

    /**
     * @param Email|string $email
     * @param ActionCodeSettings|array|null $actionCodeSettings
     *
     * @throws FailedToCreateActionLink
     */
    public function getPasswordResetLink($email, $actionCodeSettings = null): string
    {
        return $this->getEmailActionLink('PASSWORD_RESET', $email, $actionCodeSettings);
    }

    /**
     * @param Email|string $email
     * @param ActionCodeSettings|array|null $actionCodeSettings
     *
     * @throws FailedToSendActionLink
     */
    public function sendPasswordResetLink($email, $actionCodeSettings = null, string $locale = null): void
    {
        $this->sendEmailActionLink('PASSWORD_RESET', $email, $actionCodeSettings, $locale);
    }

    /**
     * @param Email|string $email
     * @param ActionCodeSettings|array|null $actionCodeSettings
     *
     * @throws FailedToCreateActionLink
     */
    public function getSignInWithEmailLink($email, $actionCodeSettings = null): string
    {
        return $this->getEmailActionLink('EMAIL_SIGNIN', $email, $actionCodeSettings);
    }

    /**
     * @param Email|string $email
     * @param ActionCodeSettings|array|null $actionCodeSettings
     *
     * @throws FailedToSendActionLink
     */
    public function sendSignInWithEmailLink($email, $actionCodeSettings = null, string $locale = null): void
    {
        $this->sendEmailActionLink('EMAIL_SIGNIN', $email, $actionCodeSettings, $locale);
    }

    /**
     * @param Uid|string $uid
     * @param array<string, mixed> $attributes
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function setCustomUserAttributes($uid, array $attributes): UserRecord
    {
        return $this->updateUser($uid, Request\UpdateUser::new()->withCustomAttributes($attributes));
    }

    /**
     * @param Uid|string $uid
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function deleteCustomUserAttributes($uid): UserRecord
    {
        return $this->updateUser($uid, Request\UpdateUser::new()->withCustomAttributes([]));
    }

    /**
     * @param Uid|string $uid
     */
    public function createCustomToken($uid, array $claims = []): Token
    {
        $uid = $uid instanceof Uid ? $uid : new Uid($uid);

        return $this->tokenGenerator->createCustomToken($uid, $claims);
    }

    public function parseToken(string $tokenString): Token
    {
        try {
            return (new Parser())->parse($tokenString);
        } catch (Throwable $e) {
            throw new InvalidArgumentException('The given token could not be parsed: '.$e->getMessage());
        }
    }

    /**
     * Verifies a JWT auth token. Returns a Promise with the tokens claims. Rejects the promise if the token
     * could not be verified. If checkRevoked is set to true, verifies if the session corresponding to the
     * ID token was revoked. If the corresponding user's session was invalidated, a RevokedToken
     * exception is thrown. If not specified the check is not applied.
     *
     * NOTE: Allowing time inconsistencies might impose a security risk. Do this only when you are not able
     * to fix your environment's time to be consistent with Google's servers. This parameter is here
     * for backwards compatibility reasons, and will be removed in the next major version. You
     * shouldn't rely on it.
     *
     * @param Token|string $idToken the JWT to verify
     * @param bool $checkIfRevoked whether to check if the ID token is revoked
     *
     * @throws \InvalidArgumentException if the token could not be parsed
     * @throws InvalidToken if the token could be parsed, but is invalid for any reason (invalid signature, expired, time errors)
     * @throws InvalidSignature if the signature doesn't match
     * @throws ExpiredToken if the token is expired
     * @throws IssuedInTheFuture if the token is issued in the future
     * @throws UnknownKey if the token's kid header doesnt' contain a known key
     */
    public function verifyIdToken($idToken, bool $checkIfRevoked = false): Token
    {
        $leewayInSeconds = 300;
        $verifier = $this->idTokenVerifier;

        if ($verifier instanceof IdTokenVerifier) {
            $verifier = $verifier->withLeewayInSeconds($leewayInSeconds);
        }

        $verifiedToken = $verifier->verifyIdToken($idToken);

        if ($checkIfRevoked) {
            try {
                $user = $this->getUser($verifiedToken->getClaim('sub'));
            } catch (Throwable $e) {
                throw new InvalidToken($verifiedToken, "Error while getting the token's user: {$e->getMessage()}", $e->getCode(), $e);
            }

            // The timestamp, in seconds, which marks a boundary, before which Firebase ID token are considered revoked.
            if (!($validSince = $user->tokensValidAfterTime ?? null)) {
                return $verifiedToken;
            }

            $tokenAuthenticatedAt = DT::toUTCDateTimeImmutable($verifiedToken->getClaim('auth_time'));
            $tokenAuthenticatedAtWithLeeway = $tokenAuthenticatedAt->modify('-'.$leewayInSeconds.' seconds');

            $validSinceWithLeeway = DT::toUTCDateTimeImmutable($validSince)->modify('-'.$leewayInSeconds.' seconds');

            if ($tokenAuthenticatedAtWithLeeway < $validSinceWithLeeway) {
                throw new RevokedIdToken($verifiedToken);
            }
        }

        return $verifiedToken;
    }

    /**
     * Verifies the given password reset code.
     *
     * @see https://firebase.google.com/docs/reference/rest/auth#section-verify-password-reset-code
     *
     * @throws ExpiredOobCode
     * @throws InvalidOobCode
     * @throws OperationNotAllowed
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function verifyPasswordResetCode(string $oobCode): void
    {
        $this->client->verifyPasswordResetCode($oobCode);
    }

    /**
     * Applies the password reset requested via the given OOB code.
     *
     * @see https://firebase.google.com/docs/reference/rest/auth#section-confirm-reset-password
     *
     * @param string $oobCode the email action code sent to the user's email for resetting the password
     * @param ClearTextPassword|string $newPassword
     * @param bool $invalidatePreviousSessions Invalidate sessions initialized with the previous credentials
     *
     * @throws ExpiredOobCode
     * @throws InvalidOobCode
     * @throws OperationNotAllowed
     * @throws UserDisabled
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function confirmPasswordReset(string $oobCode, $newPassword, bool $invalidatePreviousSessions = true): void
    {
        $newPassword = $newPassword instanceof ClearTextPassword ? $newPassword : new ClearTextPassword($newPassword);

        $response = $this->client->confirmPasswordReset($oobCode, (string) $newPassword);

        $email = JSON::decode((string) $response->getBody(), true)['email'] ?? null;

        if ($invalidatePreviousSessions && $email) {
            $this->revokeRefreshTokens($this->getUserByEmail($email)->uid);
        }
    }

    /**
     * Revokes all refresh tokens for the specified user identified by the uid provided.
     * In addition to revoking all refresh tokens for a user, all ID tokens issued
     * before revocation will also be revoked on the Auth backend. Any request with an
     * ID token generated before revocation will be rejected with a token expired error.
     *
     * @param Uid|string $uid the user whose tokens are to be revoked
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function revokeRefreshTokens($uid): void
    {
        $uid = $uid instanceof Uid ? $uid : new Uid($uid);

        $this->client->revokeRefreshTokens((string) $uid);
    }

    /**
     * @param Uid|string $uid
     * @param Provider[]|string[]|string $provider
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function unlinkProvider($uid, $provider): UserRecord
    {
        $uid = $uid instanceof Uid ? $uid : new Uid($uid);
        $provider = \array_map(static function ($provider) {
            return $provider instanceof Provider ? $provider : new Provider($provider);
        }, (array) $provider);

        $response = $this->client->unlinkProvider((string) $uid, $provider);

        return $this->getUserRecordFromResponse($response);
    }

    /**
     * @param UserRecord|Uid|string $user
     *
     * @throws FailedToSignIn
     */
    public function signInAsUser($user, array $claims = null): SignInResult
    {
        $claims = $claims ?? [];
        $uid = $user instanceof UserRecord ? $user->uid : (string) $user;

        return $this->signInHandler->handle(
            SignInWithCustomToken::fromValue((string) $this->createCustomToken($uid, $claims))
        );
    }

    /**
     * @param Token|string $token
     *
     * @throws FailedToSignIn
     */
    public function signInWithCustomToken($token): SignInResult
    {
        return $this->signInHandler->handle(
            SignInWithCustomToken::fromValue((string) $token)
        );
    }

    /**
     * @throws FailedToSignIn
     */
    public function signInWithRefreshToken(string $refreshToken): SignInResult
    {
        return $this->signInHandler->handle(
            SignInWithRefreshToken::fromValue($refreshToken)
        );
    }

    /**
     * @param string|Email $email
     * @param string|ClearTextPassword $clearTextPassword
     *
     * @throws FailedToSignIn
     */
    public function signInWithEmailAndPassword($email, $clearTextPassword): SignInResult
    {
        $email = $email instanceof Email ? (string) $email : $email;
        $clearTextPassword = $clearTextPassword instanceof ClearTextPassword ? (string) $clearTextPassword : $clearTextPassword;

        return $this->signInHandler->handle(
            SignInWithEmailAndPassword::fromValues($email, $clearTextPassword)
        );
    }

    /**
     * @param string|Email $email
     * @param string $oobCode
     *
     * @throws FailedToSignIn
     */
    public function signInWithEmailAndOobCode($email, $oobCode): SignInResult
    {
        $email = $email instanceof Email ? (string) $email : $email;

        return $this->signInHandler->handle(
            SignInWithEmailAndOobCode::fromValues($email, $oobCode)
        );
    }

    /**
     * @throws FailedToSignIn
     */
    public function signInAnonymously(): SignInResult
    {
        $result = $this->signInHandler->handle(SignInAnonymously::new());

        if ($result->idToken()) {
            return $result;
        }

        if ($uid = ($result->data()['localId'] ?? null)) {
            return $this->signInAsUser($uid);
        }

        throw new FailedToSignIn('Failed to sign in anonymously: No ID token or UID available');
    }

    /**
     * @param Provider|string $provider
     * @param UriInterface|string|null $redirectUrl
     *
     * @throws FailedToSignIn
     */
    public function signInWithIdpAccessToken($provider, string $accessToken, $redirectUrl = null): SignInResult
    {
        $provider = $provider instanceof Provider ? (string) $provider : $provider;
        $redirectUrl = $redirectUrl ?? 'http://localhost';

        if ($redirectUrl instanceof UriInterface) {
            $redirectUrl = (string) $redirectUrl;
        }

        $action = SignInWithIdpCredentials::withAccessToken($provider, $accessToken);

        if ($redirectUrl) {
            $action = $action->withRequestUri($redirectUrl);
        }

        return $this->signInHandler->handle($action);
    }

    /**
     * @param Provider|string $provider
     * @param Token|string $idToken
     * @param UriInterface|string|null $redirectUrl
     *
     * @throws FailedToSignIn
     */
    public function signInWithIdpIdToken($provider, $idToken, $redirectUrl = null): SignInResult
    {
        $provider = $provider instanceof Provider ? (string) $provider : $provider;

        if ($idToken instanceof Token) {
            $idToken = (string) $idToken;
        }

        $redirectUrl = $redirectUrl ?? 'http://localhost';

        if ($redirectUrl instanceof UriInterface) {
            $redirectUrl = (string) $redirectUrl;
        }

        $action = SignInWithIdpCredentials::withIdToken($provider, $idToken);

        if ($redirectUrl) {
            $action = $action->withRequestUri($redirectUrl);
        }

        return $this->signInHandler->handle($action);
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

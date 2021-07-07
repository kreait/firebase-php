<?php

declare(strict_types=1);

namespace Kreait\Firebase\Contract;

use DateInterval;
use Firebase\Auth\Token\Exception\ExpiredToken;
use Firebase\Auth\Token\Exception\InvalidSignature;
use Firebase\Auth\Token\Exception\InvalidToken;
use Firebase\Auth\Token\Exception\IssuedInTheFuture;
use Firebase\Auth\Token\Exception\UnknownKey;
use InvalidArgumentException;
use Kreait\Firebase\Auth\ActionCodeSettings;
use Kreait\Firebase\Auth\CreateActionLink\FailedToCreateActionLink;
use Kreait\Firebase\Auth\CreateSessionCookie\FailedToCreateSessionCookie;
use Kreait\Firebase\Auth\SendActionLink\FailedToSendActionLink;
use Kreait\Firebase\Auth\SignIn\FailedToSignIn;
use Kreait\Firebase\Auth\SignInResult;
use Kreait\Firebase\Auth\UserRecord;
use Kreait\Firebase\Exception;
use Kreait\Firebase\Exception\Auth\ExpiredOobCode;
use Kreait\Firebase\Exception\Auth\InvalidOobCode;
use Kreait\Firebase\Exception\Auth\OperationNotAllowed;
use Kreait\Firebase\Exception\Auth\RevokedIdToken;
use Kreait\Firebase\Exception\Auth\UserDisabled;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Request;
use Kreait\Firebase\Value\ClearTextPassword;
use Kreait\Firebase\Value\Email;
use Kreait\Firebase\Value\PhoneNumber;
use Kreait\Firebase\Value\Provider;
use Kreait\Firebase\Value\Uid;
use Lcobucci\JWT\Token;
use Psr\Http\Message\UriInterface;
use Traversable;

interface Auth
{
    /**
     * @param Uid|string $uid
     *
     * @throws UserNotFound
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function getUser($uid): UserRecord;

    /**
     * @param array<Uid|string> $uids
     *
     * @throws Exception\FirebaseException
     * @throws Exception\AuthException
     *
     * @return array<string, UserRecord|null>
     */
    public function getUsers(array $uids): array;

    /**
     * @throws Exception\FirebaseException
     * @throws Exception\AuthException
     *
     * @return Traversable<UserRecord>
     */
    public function listUsers(int $maxResults = 1000, int $batchSize = 1000): Traversable;

    /**
     * Creates a new user with the provided properties.
     *
     * @param array<string, mixed>|Request\CreateUser $properties
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function createUser($properties): UserRecord;

    /**
     * Updates the given user with the given properties.
     *
     * @param Uid|string $uid
     * @param array<string, mixed>|Request\UpdateUser $properties
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function updateUser($uid, $properties): UserRecord;

    /**
     * @param Email|string $email
     * @param ClearTextPassword|string $password
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function createUserWithEmailAndPassword($email, $password): UserRecord;

    /**
     * @param Email|string $email
     *
     * @throws UserNotFound
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function getUserByEmail($email): UserRecord;

    /**
     * @param PhoneNumber|string $phoneNumber
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function getUserByPhoneNumber($phoneNumber): UserRecord;

    /**
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function createAnonymousUser(): UserRecord;

    /**
     * @param Uid|string $uid
     * @param ClearTextPassword|string $newPassword
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function changeUserPassword($uid, $newPassword): UserRecord;

    /**
     * @param Uid|string $uid
     * @param Email|string $newEmail
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function changeUserEmail($uid, $newEmail): UserRecord;

    /**
     * @param Uid|string $uid
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function enableUser($uid): UserRecord;

    /**
     * @param Uid|string $uid
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function disableUser($uid): UserRecord;

    /**
     * @param Uid|string $uid
     *
     * @throws UserNotFound
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function deleteUser($uid): void;

    /**
     * @param Email|string $email
     * @param ActionCodeSettings|array<string, mixed>|null $actionCodeSettings
     *
     * @throws FailedToCreateActionLink
     */
    public function getEmailActionLink(string $type, $email, $actionCodeSettings = null): string;

    /**
     * @param Email|string $email
     * @param ActionCodeSettings|array<string, mixed>|null $actionCodeSettings
     *
     * @throws UserNotFound
     * @throws FailedToSendActionLink
     */
    public function sendEmailActionLink(string $type, $email, $actionCodeSettings = null, ?string $locale = null): void;

    /**
     * @param Email|string $email
     * @param ActionCodeSettings|array<string, mixed>|null $actionCodeSettings
     *
     * @throws FailedToCreateActionLink
     */
    public function getEmailVerificationLink($email, $actionCodeSettings = null): string;

    /**
     * @param Email|string $email
     * @param ActionCodeSettings|array<string, mixed>|null $actionCodeSettings
     *
     * @throws FailedToSendActionLink
     */
    public function sendEmailVerificationLink($email, $actionCodeSettings = null, ?string $locale = null): void;

    /**
     * @param Email|string $email
     * @param ActionCodeSettings|array<string, mixed>|null $actionCodeSettings
     *
     * @throws FailedToCreateActionLink
     */
    public function getPasswordResetLink($email, $actionCodeSettings = null): string;

    /**
     * @param Email|string $email
     * @param ActionCodeSettings|array<string, mixed>|null $actionCodeSettings
     *
     * @throws FailedToSendActionLink
     */
    public function sendPasswordResetLink($email, $actionCodeSettings = null, ?string $locale = null): void;

    /**
     * @param Email|string $email
     * @param ActionCodeSettings|array<string, mixed>|null $actionCodeSettings
     *
     * @throws FailedToCreateActionLink
     */
    public function getSignInWithEmailLink($email, $actionCodeSettings = null): string;

    /**
     * @param Email|string $email
     * @param ActionCodeSettings|array<string, mixed>|null $actionCodeSettings
     *
     * @throws FailedToSendActionLink
     */
    public function sendSignInWithEmailLink($email, $actionCodeSettings = null, ?string $locale = null): void;

    /**
     * @param Uid|string $uid
     * @param array<string, mixed> $attributes
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     *
     * @deprecated 5.4.0 use {@see setCustomUserClaims}($id, array $claims) instead
     * @see setCustomUserClaims
     * @codeCoverageIgnore
     */
    public function setCustomUserAttributes($uid, array $attributes): UserRecord;

    /**
     * @param Uid|string $uid
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     *
     * @see removeCustomUserClaims
     * @deprecated 5.4.0 use {@see setCustomUserClaims}($uid) instead
     */
    public function deleteCustomUserAttributes($uid): UserRecord;

    /**
     * Sets additional developer claims on an existing user identified by the provided UID.
     *
     * @see https://firebase.google.com/docs/auth/admin/custom-claims
     *
     * @param Uid|string $uid
     * @param array<string, mixed>|null $claims
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function setCustomUserClaims($uid, ?array $claims): void;

    /**
     * @param Uid|string $uid
     * @param array<string, mixed> $claims
     */
    public function createCustomToken($uid, array $claims = []): Token;

    public function parseToken(string $tokenString): Token;

    /**
     * Creates a new Firebase session cookie with the given lifetime.
     *
     * The session cookie JWT will have the same payload claims as the provided ID token.
     *
     * @param Token|string $idToken The Firebase ID token to exchange for a session cookie
     * @param DateInterval|int $ttl
     *
     * @throws InvalidArgumentException if the token or TTL is invalid
     * @throws FailedToCreateSessionCookie
     */
    public function createSessionCookie($idToken, $ttl): string;

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
     * @throws InvalidArgumentException if the token could not be parsed
     * @throws InvalidToken if the token could be parsed, but is invalid for any reason (invalid signature, expired, time errors)
     * @throws InvalidSignature if the signature doesn't match
     * @throws ExpiredToken if the token is expired
     * @throws IssuedInTheFuture if the token is issued in the future
     * @throws UnknownKey if the token's kid header doesnt' contain a known key
     * @throws RevokedIdToken if the token has been revoked
     */
    public function verifyIdToken($idToken, bool $checkIfRevoked = false): Token;

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
    public function verifyPasswordResetCode(string $oobCode): void;

    /**
     * Verifies the given password reset code and returns the associated user's email address.
     *
     * @see https://firebase.google.com/docs/reference/rest/auth#section-verify-password-reset-code
     *
     * @throws ExpiredOobCode
     * @throws InvalidOobCode
     * @throws OperationNotAllowed
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function verifyPasswordResetCodeAndReturnEmail(string $oobCode): Email;

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
    public function confirmPasswordReset(string $oobCode, $newPassword, bool $invalidatePreviousSessions = true): void;

    /**
     * Applies the password reset requested via the given OOB code and returns the associated user's email address.
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
    public function confirmPasswordResetAndReturnEmail(string $oobCode, $newPassword, bool $invalidatePreviousSessions = true): Email;

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
    public function revokeRefreshTokens($uid): void;

    /**
     * @param Uid|string $uid
     * @param Provider[]|string[]|string $provider
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function unlinkProvider($uid, $provider): UserRecord;

    /**
     * @param UserRecord|Uid|string $user
     * @param array<string, mixed>|null $claims
     *
     * @throws FailedToSignIn
     */
    public function signInAsUser($user, ?array $claims = null): SignInResult;

    /**
     * @param Token|string $token
     *
     * @throws FailedToSignIn
     */
    public function signInWithCustomToken($token): SignInResult;

    /**
     * @throws FailedToSignIn
     */
    public function signInWithRefreshToken(string $refreshToken): SignInResult;

    /**
     * @param string|Email $email
     * @param string|ClearTextPassword $clearTextPassword
     *
     * @throws FailedToSignIn
     */
    public function signInWithEmailAndPassword($email, $clearTextPassword): SignInResult;

    /**
     * @param string|Email $email
     *
     * @throws FailedToSignIn
     */
    public function signInWithEmailAndOobCode($email, string $oobCode): SignInResult;

    /**
     * @throws FailedToSignIn
     */
    public function signInAnonymously(): SignInResult;

    public function signInWithTwitterOauthCredential(string $accessToken, string $oauthTokenSecret, ?string $redirectUrl = null, ?string $linkingIdToken = null): SignInResult;

    public function signInWithGoogleIdToken(string $idToken, ?string $redirectUrl = null, ?string $linkingIdToken = null): SignInResult;

    public function signInWithFacebookAccessToken(string $accessToken, ?string $redirectUrl = null, ?string $linkingIdToken = null): SignInResult;

    /**
     * @see https://cloud.google.com/identity-platform/docs/reference/rest/v1/accounts/signInWithIdp
     *
     * @param Provider|string $provider
     * @param UriInterface|string|null $redirectUrl
     *
     * @throws FailedToSignIn
     */
    public function signInWithIdpAccessToken($provider, string $accessToken, $redirectUrl = null, ?string $oauthTokenSecret = null, ?string $linkingIdToken = null): SignInResult;

    /**
     * @param Provider|string $provider
     * @param Token|string $idToken
     * @param UriInterface|string|null $redirectUrl
     *
     * @throws FailedToSignIn
     */
    public function signInWithIdpIdToken($provider, $idToken, $redirectUrl = null, ?string $linkingIdToken = null): SignInResult;
}

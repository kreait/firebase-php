<?php

declare(strict_types=1);

namespace Kreait\Firebase\Contract;

use DateInterval;
use InvalidArgumentException;
use Kreait\Firebase\Auth\ActionCodeSettings;
use Kreait\Firebase\Auth\CreateActionLink\FailedToCreateActionLink;
use Kreait\Firebase\Auth\CreateSessionCookie\FailedToCreateSessionCookie;
use Kreait\Firebase\Auth\DeleteUsersResult;
use Kreait\Firebase\Auth\SendActionLink\FailedToSendActionLink;
use Kreait\Firebase\Auth\SignIn\FailedToSignIn;
use Kreait\Firebase\Auth\SignInResult;
use Kreait\Firebase\Auth\UserQuery;
use Kreait\Firebase\Auth\UserRecord;
use Kreait\Firebase\Exception;
use Kreait\Firebase\Exception\Auth\ExpiredOobCode;
use Kreait\Firebase\Exception\Auth\FailedToVerifySessionCookie;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Kreait\Firebase\Exception\Auth\InvalidOobCode;
use Kreait\Firebase\Exception\Auth\OperationNotAllowed;
use Kreait\Firebase\Exception\Auth\RevokedIdToken;
use Kreait\Firebase\Exception\Auth\RevokedSessionCookie;
use Kreait\Firebase\Exception\Auth\UserDisabled;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Request;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\UnencryptedToken;
use Psr\Http\Message\UriInterface;
use Stringable;
use Traversable;

/**
 * @phpstan-import-type UserQueryShape from UserQuery
 */
interface Auth
{
    /**
     * @param Stringable|string $uid
     *
     * @throws UserNotFound
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function getUser($uid): UserRecord;

    /**
     * @param array<Stringable|string> $uids
     *
     * @return array<string, UserRecord|null>
     *@throws Exception\AuthException
     *
     * @throws Exception\FirebaseException
     */
    public function getUsers(array $uids): array;

    /**
     * @param UserQuery|UserQueryShape $query
     *
     * @throws Exception\FirebaseException
     * @throws Exception\AuthException
     *
     * @return array<string, UserRecord>
     */
    public function queryUsers($query): array;

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
     * @param Stringable|string $uid
     * @param array<string, mixed>|Request\UpdateUser $properties
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function updateUser($uid, $properties): UserRecord;

    /**
     * @param Stringable|string $email
     * @param Stringable|string $password
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function createUserWithEmailAndPassword($email, $password): UserRecord;

    /**
     * @param Stringable|string $email
     *
     * @throws UserNotFound
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function getUserByEmail($email): UserRecord;

    /**
     * @param Stringable|string $phoneNumber
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
     * @param Stringable|string $uid
     * @param Stringable|string $newPassword
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function changeUserPassword($uid, $newPassword): UserRecord;

    /**
     * @param Stringable|string $uid
     * @param Stringable|string $newEmail
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function changeUserEmail($uid, $newEmail): UserRecord;

    /**
     * @param Stringable|string $uid
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function enableUser($uid): UserRecord;

    /**
     * @param Stringable|string $uid
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function disableUser($uid): UserRecord;

    /**
     * @param Stringable|string $uid
     *
     * @throws UserNotFound
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function deleteUser($uid): void;

    /**
     * @param iterable<Stringable|string> $uids
     * @param bool $forceDeleteEnabledUsers Whether to force deleting accounts that are not in disabled state. If false, only disabled accounts will be deleted, and accounts that are not disabled will be added to the errors.
     *
     * @throws Exception\AuthException
     */
    public function deleteUsers(iterable $uids, bool $forceDeleteEnabledUsers = false): DeleteUsersResult;

    /**
     * @param Stringable|string $email
     * @param ActionCodeSettings|array<string, mixed>|null $actionCodeSettings
     *
     * @throws FailedToCreateActionLink
     */
    public function getEmailActionLink(string $type, $email, $actionCodeSettings = null, ?string $locale = null): string;

    /**
     * @param Stringable|string $email
     * @param ActionCodeSettings|array<string, mixed>|null $actionCodeSettings
     *
     * @throws UserNotFound
     * @throws FailedToSendActionLink
     */
    public function sendEmailActionLink(string $type, $email, $actionCodeSettings = null, ?string $locale = null): void;

    /**
     * @param Stringable|string $email
     * @param ActionCodeSettings|array<string, mixed>|null $actionCodeSettings
     *
     * @throws FailedToCreateActionLink
     */
    public function getEmailVerificationLink($email, $actionCodeSettings = null, ?string $locale = null): string;

    /**
     * @param Stringable|string $email
     * @param ActionCodeSettings|array<string, mixed>|null $actionCodeSettings
     *
     * @throws FailedToSendActionLink
     */
    public function sendEmailVerificationLink($email, $actionCodeSettings = null, ?string $locale = null): void;

    /**
     * @param Stringable|string $email
     * @param ActionCodeSettings|array<string, mixed>|null $actionCodeSettings
     *
     * @throws FailedToCreateActionLink
     */
    public function getPasswordResetLink($email, $actionCodeSettings = null, ?string $locale = null): string;

    /**
     * @param Stringable|string $email
     * @param ActionCodeSettings|array<string, mixed>|null $actionCodeSettings
     *
     * @throws FailedToSendActionLink
     */
    public function sendPasswordResetLink($email, $actionCodeSettings = null, ?string $locale = null): void;

    /**
     * @param Stringable|string $email
     * @param ActionCodeSettings|array<string, mixed>|null $actionCodeSettings
     *
     * @throws FailedToCreateActionLink
     */
    public function getSignInWithEmailLink($email, $actionCodeSettings = null, ?string $locale = null): string;

    /**
     * @param Stringable|string $email
     * @param ActionCodeSettings|array<string, mixed>|null $actionCodeSettings
     *
     * @throws FailedToSendActionLink
     */
    public function sendSignInWithEmailLink($email, $actionCodeSettings = null, ?string $locale = null): void;

    /**
     * Sets additional developer claims on an existing user identified by the provided UID.
     *
     * @see https://firebase.google.com/docs/auth/admin/custom-claims
     *
     * @param Stringable|string $uid
     * @param array<string, mixed>|null $claims
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function setCustomUserClaims($uid, ?array $claims): void;

    /**
     * @param Stringable|string $uid
     * @param array<string, mixed> $claims
     * @param int|DateInterval|string $ttl
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function createCustomToken($uid, array $claims = [], $ttl = 3600): UnencryptedToken;

    public function parseToken(string $tokenString): UnencryptedToken;

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
     * Verifies a JWT auth token.
     *
     * Returns a token with the token's claims or rejects it if the token could not be verified.
     *
     * If checkRevoked is set to true, verifies if the session corresponding to the ID token was revoked.
     * If the corresponding user's session was invalidated, a RevokedIdToken exception is thrown.
     * If not specified the check is not applied.
     *
     * NOTE: Allowing time inconsistencies might impose a security risk. Do this only when you are not able
     * to fix your environment's time to be consistent with Google's servers.
     *
     * @param Token|string $idToken the JWT to verify
     * @param bool $checkIfRevoked whether to check if the ID token is revoked
     * @param positive-int|null $leewayInSeconds number of seconds to allow a token to be expired, in case that there
     *                                           is a clock skew between the signing and the verifying server
     *
     * @throws FailedToVerifyToken if the token could not be verified
     * @throws RevokedIdToken if the token has been revoked
     */
    public function verifyIdToken($idToken, bool $checkIfRevoked = false, int $leewayInSeconds = null): UnencryptedToken;

    /**
     * Verifies a JWT session cookie.
     *
     * Returns a token with the cookie's claims or rejects it if the session cookie could not be verified.
     *
     * If checkRevoked is set to true, verifies if the session corresponding to the ID token was revoked.
     * If the corresponding user's session was invalidated, a RevokedSessionCookie exception is thrown.
     * If not specified the check is not applied.
     *
     * NOTE: Allowing time inconsistencies might impose a security risk. Do this only when you are not able
     * to fix your environment's time to be consistent with Google's servers.
     *
     * @param positive-int|null $leewayInSeconds
     *
     * @throws FailedToVerifySessionCookie
     * @throws RevokedSessionCookie
     */
    public function verifySessionCookie(string $sessionCookie, bool $checkIfRevoked = false, ?int $leewayInSeconds = null): UnencryptedToken;

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
    public function verifyPasswordResetCode(string $oobCode): string;

    /**
     * Applies the password reset requested via the given OOB code and returns the associated user's email address.
     *
     * @see https://firebase.google.com/docs/reference/rest/auth#section-confirm-reset-password
     *
     * @param string $oobCode the email action code sent to the user's email for resetting the password
     * @param Stringable|string $newPassword
     * @param bool $invalidatePreviousSessions Invalidate sessions initialized with the previous credentials
     *
     * @throws ExpiredOobCode
     * @throws InvalidOobCode
     * @throws OperationNotAllowed
     * @throws UserDisabled
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function confirmPasswordReset(string $oobCode, $newPassword, bool $invalidatePreviousSessions = true): string;

    /**
     * Revokes all refresh tokens for the specified user identified by the uid provided.
     * In addition to revoking all refresh tokens for a user, all ID tokens issued
     * before revocation will also be revoked on the Auth backend. Any request with an
     * ID token generated before revocation will be rejected with a token expired error.
     *
     * @param Stringable|string $uid the user whose tokens are to be revoked
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function revokeRefreshTokens($uid): void;

    /**
     * @param Stringable|string $uid
     * @param Stringable[]|string[]|Stringable|string $provider
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function unlinkProvider($uid, $provider): UserRecord;

    /**
     * @param UserRecord|Stringable|string $user
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
     * @param Stringable|string $email
     * @param Stringable|string $clearTextPassword
     *
     * @throws FailedToSignIn
     */
    public function signInWithEmailAndPassword($email, $clearTextPassword): SignInResult;

    /**
     * @param Stringable|string $email
     *
     * @throws FailedToSignIn
     */
    public function signInWithEmailAndOobCode($email, string $oobCode): SignInResult;

    /**
     * @throws FailedToSignIn
     */
    public function signInAnonymously(): SignInResult;

    /**
     * @see https://cloud.google.com/identity-platform/docs/reference/rest/v1/accounts/signInWithIdp
     *
     * @param Stringable|string $provider
     * @param UriInterface|string|null $redirectUrl
     *
     * @throws FailedToSignIn
     */
    public function signInWithIdpAccessToken($provider, string $accessToken, $redirectUrl = null, ?string $oauthTokenSecret = null, ?string $linkingIdToken = null, ?string $rawNonce = null): SignInResult;

    /**
     * @param Stringable|string $provider
     * @param Token|string $idToken
     * @param UriInterface|string|null $redirectUrl
     *
     * @throws FailedToSignIn
     */
    public function signInWithIdpIdToken($provider, $idToken, $redirectUrl = null, ?string $linkingIdToken = null, ?string $rawNonce = null): SignInResult;
}

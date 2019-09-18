<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use Firebase\Auth\Token\Domain\Generator as TokenGenerator;
use Firebase\Auth\Token\Domain\Verifier;
use Firebase\Auth\Token\Exception\InvalidToken;
use Firebase\Auth\Token\Exception\UnknownKey;
use Generator;
use Kreait\Firebase\Auth\ApiClient;
use Kreait\Firebase\Auth\IdTokenVerifier;
use Kreait\Firebase\Auth\LinkedProviderData;
use Kreait\Firebase\Auth\UserRecord;
use Kreait\Firebase\Exception\Auth\InvalidPassword;
use Kreait\Firebase\Exception\Auth\RevokedIdToken;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Util\DT;
use Kreait\Firebase\Util\JSON;
use Kreait\Firebase\Value\ClearTextPassword;
use Kreait\Firebase\Value\Email;
use Kreait\Firebase\Value\PhoneNumber;
use Kreait\Firebase\Value\Provider;
use Kreait\Firebase\Value\Uid;
use Lcobucci\JWT\Token;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class Auth
{
    /**
     * @var ApiClient
     */
    private $client;

    /**
     * @var TokenGenerator
     */
    private $tokenGenerator;

    /**
     * @var Verifier
     */
    private $idTokenVerifier;

    /**
     * @internal
     */
    public function __construct(ApiClient $client, TokenGenerator $customToken, Verifier $idTokenVerifier)
    {
        $this->client = $client;
        $this->tokenGenerator = $customToken;
        $this->idTokenVerifier = $idTokenVerifier;
    }

    /**
     * @internal
     */
    public function getApiClient(): ApiClient
    {
        return $this->client;
    }

    /**
     * @param Uid|string $uid
     *
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
        return $this->createUser(
            Request\CreateUser::new()
                ->withUnverifiedEmail($email)
                ->withClearTextPassword($password)
        );
    }

    /**
     * @param Email|string $email
     *
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
    public function deleteUser($uid)
    {
        $uid = $uid instanceof Uid ? $uid : new Uid($uid);

        try {
            $this->client->deleteUser((string) $uid);
        } catch (UserNotFound $e) {
            throw new UserNotFound("No user with uid '{$uid}' found.");
        }
    }

    /**
     * @param Uid|string $uid
     * @param UriInterface|string|null $continueUrl
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function sendEmailVerification($uid, $continueUrl = null, string $locale = null)
    {
        if ($continueUrl !== null) {
            $continueUrl = (string) $continueUrl;
        }

        $response = $this->client->exchangeCustomTokenForIdAndRefreshToken(
            $this->createCustomToken($uid)
        );

        $idToken = JSON::decode((string) $response->getBody(), true)['idToken'];

        $this->client->sendEmailVerification($idToken, $continueUrl, $locale);
    }

    /**
     * @param Email|mixed $email
     * @param UriInterface|string|null $continueUrl
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function sendPasswordResetEmail($email, $continueUrl = null, string $locale = null)
    {
        if ($continueUrl !== null) {
            $continueUrl = (string) $continueUrl;
        }

        $email = $email instanceof Email ? $email : new Email((string) $email);

        $this->client->sendPasswordResetEmail((string) $email, (string) $continueUrl, $locale);
    }

    /**
     * @param Uid|string $uid
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
     * @param bool $allowTimeInconsistencies Deprecated since 4.31
     *
     * @throws InvalidToken
     * @throws UnknownKey if the token's kid header doesnt' contain a known key
     * @throws Exception\FirebaseException
     * @throws Exception\AuthException
     */
    public function verifyIdToken($idToken, bool $checkIfRevoked = false, /* @deprecated */ bool $allowTimeInconsistencies = null): Token
    {
        // @codeCoverageIgnoreStart
        if (\is_bool($allowTimeInconsistencies)) {
            // @see https://github.com/firebase/firebase-admin-dotnet/pull/29
            \trigger_error(
                'The parameter $allowTimeInconsistencies is deprecated and was replaced with a default leeway of 300 seconds.',
                \E_USER_DEPRECATED
            );
        }
        // @codeCoverageIgnoreEnd

        $leewayInSeconds = 300;
        $verifier = $this->idTokenVerifier;

        if ($verifier instanceof IdTokenVerifier) {
            $verifier = $verifier->withLeewayInSeconds($leewayInSeconds);
        }

        $verifiedToken = $verifier->verifyIdToken($idToken);

        if ($checkIfRevoked) {
            $tokenAuthenticatedAt = DT::toUTCDateTimeImmutable($verifiedToken->getClaim('auth_time'));
            $tokenAuthenticatedAt = $tokenAuthenticatedAt->modify('-'.$leewayInSeconds.' seconds');

            $validSince = $this->getUser($verifiedToken->getClaim('sub'))->tokensValidAfterTime;

            if ($validSince && ($tokenAuthenticatedAt < $validSince)) {
                throw new RevokedIdToken($verifiedToken);
            }
        }

        return $verifiedToken;
    }

    /**
     * Verifies wether the given email/password combination is correct and returns
     * a UserRecord when it is, an Exception otherwise.
     *
     * This method has the side effect of changing the last login timestamp of the
     * given user. The recommended way to authenticate users in a client/server
     * environment is to use a Firebase Client SDK to authenticate the user
     * and to send an ID Token generated by the client back to the server.
     *
     * @param Email|string $email
     * @param ClearTextPassword|string $password
     *
     * @throws InvalidPassword if the given password does not match the given email address
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     *
     * @return UserRecord if the combination of email and password is correct
     */
    public function verifyPassword($email, $password): UserRecord
    {
        $email = $email instanceof Email ? $email : new Email($email);
        $password = $password instanceof ClearTextPassword ? $password : new ClearTextPassword($password);

        $response = $this->client->verifyPassword((string) $email, (string) $password);

        return $this->getUserRecordFromResponse($response);
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
    public function revokeRefreshTokens($uid)
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
     * Logs in the user to Firebase by a provider's access token (like Google, Facebook, Twitter, etc),
     * if the authentication provider is enabled for the project.
     *
     * First, you have to get a valid access token for your provider manually.
     *
     * @param Provider|string $provider
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function linkProviderThroughAccessToken($provider, string $accessToken): LinkedProviderData
    {
        $provider = $provider instanceof Provider ? $provider : new Provider($provider);
        $response = $this->client->linkProviderThroughAccessToken($provider, $accessToken);

        return LinkedProviderData::fromResponseData(
            $this->getUserRecordFromResponse($response),
            JSON::decode((string) $response->getBody(), true)
        );
    }

    /**
     * Logs in the user to Firebase by a provider's ID token (like Google, Facebook, Twitter, etc),
     * if the authentication provider is enabled for the project.
     *
     * First, you have to get a valid ID token for your provider manually.
     *
     * @param Provider|string $provider
     *
     * @throws Exception\AuthException
     * @throws Exception\FirebaseException
     */
    public function linkProviderThroughIdToken($provider, string $idToken): LinkedProviderData
    {
        $provider = $provider instanceof Provider ? $provider : new Provider($provider);
        $response = $this->client->linkProviderThroughIdToken($provider, $idToken);

        return LinkedProviderData::fromResponseData(
            $this->getUserRecordFromResponse($response),
            JSON::decode((string) $response->getBody(), true)
        );
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

<?php

namespace Kreait\Firebase;

use Firebase\Auth\Token\Domain\Generator as TokenGenerator;
use Firebase\Auth\Token\Domain\Verifier as IdTokenVerifier;
use Kreait\Firebase\Auth\ApiClient;
use Kreait\Firebase\Auth\UserRecord;
use Kreait\Firebase\Exception\Auth\InvalidPassword;
use Kreait\Firebase\Exception\Auth\RevokedIdToken;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Util\DT;
use Kreait\Firebase\Util\JSON;
use Kreait\Firebase\Value\ClearTextPassword;
use Kreait\Firebase\Value\Email;
use Kreait\Firebase\Value\PhoneNumber;
use Kreait\Firebase\Value\Uid;
use Lcobucci\JWT\Token;

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
     * @var IdTokenVerifier
     */
    private $idTokenVerifier;

    public function __construct(ApiClient $client, TokenGenerator $customToken, IdTokenVerifier $idTokenVerifier)
    {
        $this->client = $client;
        $this->tokenGenerator = $customToken;
        $this->idTokenVerifier = $idTokenVerifier;
    }

    public function getApiClient(): ApiClient
    {
        return $this->client;
    }

    public function getUser($uid): UserRecord
    {
        $uid = $uid instanceof Uid ? $uid : new Uid($uid);

        $response = $this->client->getAccountInfo((string) $uid);

        $data = JSON::decode((string) $response->getBody(), true);

        if (!array_key_exists('users', $data) || !\count($data['users'])) {
            throw UserNotFound::withCustomMessage('No user with uid "'.$uid.'" found.');
        }

        return UserRecord::fromResponseData($data['users'][0]);
    }

    /**
     * @param int $maxResults
     * @param int $batchSize
     *
     * @return \Generator|UserRecord[]
     */
    public function listUsers(int $maxResults = 1000, int $batchSize = 1000): \Generator
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
     * @throws InvalidArgumentException if invalid properties have been provided
     *
     * @return UserRecord
     */
    public function createUser($properties): UserRecord
    {
        $request = $properties instanceof Request\CreateUser
            ? $properties
            : Request\CreateUser::withProperties($properties);

        $response = $this->client->createUser($request);

        $uid = JSON::decode((string) $response->getBody(), true)['localId'];

        return $this->getUser($uid);
    }

    /**
     * Updates the given user with the given properties.
     *
     * @param mixed|Uid $uid
     * @param array|Request\UpdateUser $properties
     *
     * @throws InvalidArgumentException if invalid properties have been provided
     *
     * @return UserRecord
     */
    public function updateUser($uid, $properties): UserRecord
    {
        $request = $properties instanceof Request\UpdateUser
            ? $properties
            : Request\UpdateUser::withProperties($properties);

        $request = $request->withUid($uid instanceof Uid ? $uid : new Uid((string) $uid));

        $response = $this->client->updateUser($request);

        $uid = JSON::decode((string) $response->getBody(), true)['localId'];

        return $this->getUser($uid);
    }

    public function createUserWithEmailAndPassword(string $email, string $password): UserRecord
    {
        return $this->createUser(
            Request\CreateUser::new()
                ->withUnverifiedEmail($email)
                ->withClearTextPassword($password)
        );
    }

    public function getUserByEmail($email): UserRecord
    {
        $email = $email instanceof Email ? $email : new Email($email);

        $response = $this->client->getUserByEmail((string) $email);

        $data = JSON::decode((string) $response->getBody(), true);

        if (!array_key_exists('users', $data) || !\count($data['users'])) {
            throw UserNotFound::withCustomMessage('No user with email "'.$email.'" found.');
        }

        return UserRecord::fromResponseData($data['users'][0]);
    }

    public function getUserByPhoneNumber($phoneNumber): UserRecord
    {
        $phoneNumber = $phoneNumber instanceof PhoneNumber ? $phoneNumber : new PhoneNumber($phoneNumber);

        $response = $this->client->getUserByPhoneNumber((string) $phoneNumber);

        $data = JSON::decode((string) $response->getBody(), true);

        if (!array_key_exists('users', $data) || !\count($data['users'])) {
            throw UserNotFound::withCustomMessage('No user with phone number "'.$phoneNumber.'" found.');
        }

        return UserRecord::fromResponseData($data['users'][0]);
    }

    public function createAnonymousUser(): UserRecord
    {
        return $this->createUser(Request\CreateUser::new());
    }

    public function changeUserPassword(string $uid, string $newPassword): UserRecord
    {
        return $this->updateUser($uid, Request\UpdateUser::new()->withClearTextPassword($newPassword));
    }

    public function changeUserEmail(string $uid, string $newEmail): UserRecord
    {
        return $this->updateUser($uid, Request\UpdateUser::new()->withEmail($newEmail));
    }

    public function enableUser(string $uid): UserRecord
    {
        return $this->updateUser($uid, Request\UpdateUser::new()->markAsEnabled());
    }

    public function disableUser(string $uid): UserRecord
    {
        return $this->updateUser($uid, Request\UpdateUser::new()->markAsDisabled());
    }

    public function deleteUser(string $uid)
    {
        try {
            $this->client->deleteUser($uid);
        } catch (UserNotFound $e) {
            throw UserNotFound::withCustomMessage('No user with uid "'.$uid.'" found.');
        }
    }

    /**
     * @param string $uid
     */
    public function sendEmailVerification(string $uid)
    {
        $response = $this->client->exchangeCustomTokenForIdAndRefreshToken(
            $this->createCustomToken($uid)
        );

        $idToken = JSON::decode((string) $response->getBody(), true)['idToken'];

        $this->client->sendEmailVerification($idToken);
    }

    public function sendPasswordResetEmail(string $email)
    {
        $this->client->sendPasswordResetEmail($email);
    }

    public function createCustomToken($uid, array $claims = []): Token
    {
        return $this->tokenGenerator->createCustomToken($uid, $claims);
    }

    /**
     * Verifies a JWT auth token. Returns a Promise with the tokens claims. Rejects the promise if the token
     * could not be verified. If checkRevoked is set to true, verifies if the session corresponding to the
     * ID token was revoked. If the corresponding user's session was invalidated, a RevokedToken
     * exception is thrown. If not specified the check is not applied.
     *
     * @param Token|string $idToken the JWT to verify
     * @param bool $checkIfRevoked whether to check if the ID token is revoked
     *
     * @throws RevokedIdToken
     *
     * @return Token the verified token
     */
    public function verifyIdToken($idToken, bool $checkIfRevoked = false): Token
    {
        $verifiedToken = $this->idTokenVerifier->verifyIdToken($idToken);

        if ($checkIfRevoked) {
            $tokenAuthenticatedAt = DT::toUTCDateTimeImmutable($verifiedToken->getClaim('auth_time'));
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
     *
     * @return UserRecord if the combination of email and password is correct
     */
    public function verifyPassword($email, $password): UserRecord
    {
        $email = $email instanceof Email ? $email : new Email($email);
        $password = $password instanceof ClearTextPassword ? $password : new ClearTextPassword($password);

        $response = $this->client->verifyPassword((string) $email, (string) $password);

        $uid = JSON::decode((string) $response->getBody(), true)['localId'];

        return $this->getUser($uid);
    }

    /**
     * Revokes all refresh tokens for the specified user identified by the uid provided.
     * In addition to revoking all refresh tokens for a user, all ID tokens issued
     * before revocation will also be revoked on the Auth backend. Any request with an
     * ID token generated before revocation will be rejected with a token expired error.
     *
     * @param string $uid the user whose tokens are to be revoked
     */
    public function revokeRefreshTokens(string $uid)
    {
        $this->client->revokeRefreshTokens($uid);
    }
}

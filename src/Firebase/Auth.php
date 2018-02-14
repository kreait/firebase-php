<?php

namespace Kreait\Firebase;

use Firebase\Auth\Token\Domain\Generator as TokenGenerator;
use Firebase\Auth\Token\Domain\Verifier as IdTokenVerifier;
use Kreait\Firebase\Auth\ApiClient;
use Kreait\Firebase\Auth\UserRecord;
use Kreait\Firebase\Exception\Auth\RevokedIdToken;
use Kreait\Firebase\Util\JSON;
use Kreait\Firebase\Util\Util;
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
        $response = $this->client->getAccountInfo($uid);

        $data = JSON::decode((string) $response->getBody(), true)['users'][0];

        return UserRecord::fromResponseData($data);
    }

    public function getUserInfo(string $uid): array
    {
        $response = $this->client->getAccountInfo($uid);

        $data = JSON::decode((string) $response->getBody(), true);

        return array_shift($data['users']);
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

            foreach ((array) $result['users'] as $userData) {
                yield UserRecord::fromResponseData($userData);

                if (++$count === $maxResults) {
                    return;
                }
            }

            $pageToken = $result['nextPageToken'] ?? null;
        } while ($pageToken);
    }

    public function createUserWithEmailAndPassword(string $email, string $password): UserRecord
    {
        $response = $this->client->signupNewUser($email, $password);

        $uid = JSON::decode((string) $response->getBody(), true)['localId'];

        return $this->getUser($uid);
    }

    public function getUserByEmail(string $email): UserRecord
    {
        $response = $this->client->getUserByEmail($email);

        $data = JSON::decode((string) $response->getBody(), true)['users'][0];

        return UserRecord::fromResponseData($data);
    }

    public function createAnonymousUser(): UserRecord
    {
        $response = $this->client->signupNewUser();

        // The response for a created user only includes the local id,
        // so we have to refetch them.
        $uid = JSON::decode((string) $response->getBody(), true)['localId'];

        return $this->getUser($uid);
    }

    public function changeUserPassword(string $uid, string $newPassword): UserRecord
    {
        $this->client->changeUserPassword($uid, $newPassword);

        return $this->getUser($uid);
    }

    public function changeUserEmail(string $uid, string $newEmail): UserRecord
    {
        $this->client->changeUserEmail($uid, $newEmail);

        return $this->getUser($uid);
    }

    public function enableUser(string $uid): UserRecord
    {
        $this->client->enableUser($uid);

        return $this->getUser($uid);
    }

    public function disableUser(string $uid): UserRecord
    {
        $this->client->disableUser($uid);

        return $this->getUser($uid);
    }

    public function deleteUser(string $uid)
    {
        $this->client->deleteUser($uid);
    }

    /**
     * @param string $uid
     */
    public function sendEmailVerification(string $uid): void
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
            $tokenAuthenticatedAt = Util::parseTimestamp($verifiedToken->getClaim('auth_time'));
            $validSince = $this->getUser($verifiedToken->getClaim('sub'))->tokensValidAfterTime;

            if ($validSince && ($tokenAuthenticatedAt < $validSince)) {
                throw new RevokedIdToken($verifiedToken);
            }
        }

        return $verifiedToken;
    }

    /**
     * Revokes all refresh tokens for the specified user identified by the uid provided.
     * In addition to revoking all refresh tokens for a user, all ID tokens issued
     * before revocation will also be revoked on the Auth backend. Any request with an
     * ID token generated before revocation will be rejected with a token expired error.
     *
     * @param string $uid the user whose tokens are to be revoked
     */
    public function revokeRefreshTokens(string $uid): void
    {
        $this->client->revokeRefreshTokens($uid);
    }
}

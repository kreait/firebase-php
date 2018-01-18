<?php

namespace Kreait\Firebase;

use Kreait\Firebase\Auth\ApiClient;
use Kreait\Firebase\Auth\CustomTokenGenerator;
use Kreait\Firebase\Auth\IdTokenVerifier;
use Kreait\Firebase\Auth\User;
use Kreait\Firebase\Exception\Auth\RevokedIdToken;
use Kreait\Firebase\Util\JSON;
use Lcobucci\JWT\Token;
use Psr\Http\Message\ResponseInterface;

class Auth
{
    /**
     * @var ApiClient
     */
    private $client;

    /**
     * @var CustomTokenGenerator
     */
    private $customToken;

    /**
     * @var IdTokenVerifier
     */
    private $idTokenVerifier;

    public function __construct(ApiClient $client, CustomTokenGenerator $customToken, IdTokenVerifier $idTokenVerifier)
    {
        $this->client = $client;
        $this->customToken = $customToken;
        $this->idTokenVerifier = $idTokenVerifier;
    }

    public function getApiClient(): ApiClient
    {
        return $this->client;
    }

    public function getUser($uid, array $claims = []): User
    {
        $response = $this->client->exchangeCustomTokenForIdAndRefreshToken(
            $this->createCustomToken($uid, $claims)
        );

        return $this->convertResponseToUser($response);
    }

    public function getUserInfo($userOrUid): array
    {
        $response = $this->client->getAccountInfo($this->uid($userOrUid));

        $data = JSON::decode($response->getBody()->getContents(), true);

        return array_shift($data['users']);
    }

    public function listUsers(int $maxResults = 1000, int $batchSize = 1000): \Generator
    {
        $pageToken = null;
        $count = 0;

        do {
            $response = $this->client->downloadAccount($batchSize, $pageToken);
            $result = JSON::decode((string) $response->getBody(), true);

            foreach ((array) ($result['users'] ?? []) as $userData) {
                yield $userData;

                if (++$count === $maxResults) {
                    return;
                }
            }

            $pageToken = $result['nextPageToken'] ?? null;
        } while ($pageToken);
    }

    public function createUserWithEmailAndPassword(string $email, string $password): User
    {
        $this->client->signupNewUser($email, $password);

        // The response for a created user only includes the local id,
        // so we have to refetch them.
        return $this->getUserByEmailAndPassword($email, $password);
    }

    public function getUserByEmailAndPassword(string $email, string $password): User
    {
        $response = $this->client->getUserByEmailAndPassword($email, $password);

        return $this->convertResponseToUser($response);
    }

    public function createAnonymousUser(): User
    {
        $response = $this->client->signupNewUser();

        // The response for a created user only includes the local id,
        // so we have to refetch them.
        $uid = JSON::decode((string) $response->getBody(), true)['localId'];

        return $this->getUser($uid);
    }

    public function changeUserPassword($userOrUid, string $newPassword): User
    {
        $this->client->changeUserPassword($uid = $this->uid($userOrUid), $newPassword);

        return $this->getUser($uid);
    }

    public function changeUserEmail($userOrUid, string $newEmail): User
    {
        $this->client->changeUserEmail($uid = $this->uid($userOrUid), $newEmail);

        return $this->getUser($uid);
    }

    public function enableUser($userOrUid): User
    {
        $this->client->enableUser($uid = $this->uid($userOrUid));

        return $this->getUser($uid);
    }

    public function disableUser($userOrUid)
    {
        $this->client->disableUser($uid = $this->uid($userOrUid));
    }

    public function deleteUser($userOrUid): User
    {
        $this->client->deleteUser($uid = $this->uid($userOrUid));

        return $this->getUser($uid);
    }

    /**
     * @deprecated 3.9
     *
     * @param User $user
     */
    public function sendEmailVerification(User $user)
    {
        $this->client->sendEmailVerification($user);
    }

    public function sendPasswordResetEmail($userOrEmail)
    {
        $this->client->sendPasswordResetEmail($this->email($userOrEmail));
    }

    public function createCustomToken($uid, array $claims = [], \DateTimeInterface $expiresAt = null): Token
    {
        return $this->customToken->create($uid, $claims, $expiresAt);
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
        $verifiedToken = $this->idTokenVerifier->verify($idToken);

        if ($checkIfRevoked) {
            $response = $this->client->getAccountInfo($verifiedToken->getClaim('sub'));
            $data = JSON::decode($response->getBody()->getContents(), true);

            if ($data['users'][0]['validSince'] ?? null) {
                $validSince = (int) $data['users'][0]['validSince'];
                $tokenAuthenticatedAt = (int) $verifiedToken->getClaim('auth_time');

                if ($tokenAuthenticatedAt < $validSince) {
                    throw new RevokedIdToken($verifiedToken);
                }
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
     * @param User|string $userOrUid the user whose tokens are to be revoked
     *
     * @return string the user id of the corresponding user
     */
    public function revokeRefreshTokens($userOrUid): string
    {
        $this->client->revokeRefreshTokens($uid = $this->uid($userOrUid));

        return $uid;
    }

    private function convertResponseToUser(ResponseInterface $response): User
    {
        $data = JSON::decode((string) $response->getBody(), true);

        return User::create($data['idToken'], $data['refreshToken']);
    }

    private function uid($userOrUid): string
    {
        if ($userOrUid instanceof User) {
            trigger_error(
                sprintf(
                    'The usage of %s as a parameter for %s is deprecated. Use a UID string directly.',
                    User::class, self::class
                ),
                E_USER_DEPRECATED
            );

            return $userOrUid->getUid();
        }

        return (string) $userOrUid;
    }

    private function email($userOrEmail): string
    {
        if ($userOrEmail instanceof User) {
            trigger_error(
                sprintf(
                    'The usage of %s as a parameter for %s is deprecated. Use an email string directly.',
                    User::class, self::class
                ),
                E_USER_DEPRECATED
            );

            return $userOrEmail->getEmail();
        }

        return (string) $userOrEmail;
    }
}

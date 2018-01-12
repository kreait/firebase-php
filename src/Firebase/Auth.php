<?php

namespace Kreait\Firebase;

use Kreait\Firebase\Auth\ApiClient;
use Kreait\Firebase\Auth\CustomTokenGenerator;
use Kreait\Firebase\Auth\IdTokenVerifier;
use Kreait\Firebase\Auth\User;
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

    public function changeUserPassword(User $user, string $newPassword): User
    {
        $response = $this->client->changeUserPassword($user, $newPassword);

        return $this->convertResponseToUser($response);
    }

    public function changeUserEmail(User $user, string $newEmail): User
    {
        $response = $this->client->changeUserEmail($user, $newEmail);

        return $this->convertResponseToUser($response);
    }

    public function deleteUser(User $user)
    {
        $this->client->deleteUser($user);
    }

    public function sendEmailVerification(User $user)
    {
        $this->client->sendEmailVerification($user);
    }

    public function sendPasswordResetEmail($userOrEmail)
    {
        $email = $userOrEmail instanceof User
            ? $userOrEmail->getEmail()
            : (string) $userOrEmail;

        $this->client->sendPasswordResetEmail($email);
    }

    public function createCustomToken($uid, array $claims = [], \DateTimeInterface $expiresAt = null): Token
    {
        return $this->customToken->create($uid, $claims, $expiresAt);
    }

    public function verifyIdToken($idToken): Token
    {
        return $this->idTokenVerifier->verify($idToken);
    }

    private function convertResponseToUser(ResponseInterface $response): User
    {
        $data = JSON::decode((string) $response->getBody(), true);

        return User::create($data['idToken'], $data['refreshToken']);
    }
}

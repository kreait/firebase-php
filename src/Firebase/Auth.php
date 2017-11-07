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

    public function createUserWithEmailAndPassword(string $email, string $password): User
    {
        $response = $this->client->signupNewUser($email, $password);

        return $this->convertResponseToUser($response);
    }

    public function getUserByEmailAndPassword(string $email, string $password): User
    {
        $response = $this->client->getUserByEmailAndPassword($email, $password);

        return $this->convertResponseToUser($response);
    }

    public function createAnonymousUser(): User
    {
        return $this->createUserWithEmailAndPassword('', '');
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

    public function deleteUser(User $user): void
    {
        $this->client->deleteUser($user);
    }

    public function sendEmailVerification(User $user): void
    {
        $this->client->sendEmailVerification($user);
    }

    public function sendPasswordResetEmail($userOrEmail): void
    {
        $email = $userOrEmail instanceof User
            ? $userOrEmail->getUid()
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

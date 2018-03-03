<?php

namespace Kreait\Firebase\Auth;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Kreait\Firebase\Exception\Auth\CredentialsMismatch;
use Kreait\Firebase\Exception\Auth\EmailNotFound;
use Kreait\Firebase\Exception\Auth\InvalidCustomToken;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Request\CreateUser;
use Lcobucci\JWT\Token;
use Psr\Http\Message\ResponseInterface;

class ApiClient
{
    /**
     * @var ClientInterface
     */
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Takes a custom token and exchanges it with an ID token.
     *
     * @param Token $token
     *
     * @see https://firebase.google.com/docs/reference/rest/auth/#section-verify-custom-token
     *
     * @throws InvalidCustomToken
     * @throws CredentialsMismatch
     *
     * @return ResponseInterface
     */
    public function exchangeCustomTokenForIdAndRefreshToken(Token $token): ResponseInterface
    {
        return $this->request('verifyCustomToken', [
            'token' => (string) $token,
            'returnSecureToken' => true,
        ]);
    }

    public function createUser(CreateUser $request): ResponseInterface
    {
        return $this->request('signupNewUser', $request);
    }

    /**
     * @deprecated 4.2.0
     * @see ApiClient::createUser()
     *
     * @codeCoverageIgnore
     */
    public function signupNewUser(string $email = null, string $password = null): ResponseInterface
    {
        return $this->createUser(
            CreateUser::new()
                ->withUnverifiedEmail($email)
                ->withClearTextPassword($password)
        );
    }

    /**
     * Returns a user for the given email address.
     *
     * @param string $email
     *
     * @throws EmailNotFound
     *
     * @return ResponseInterface
     */
    public function getUserByEmail(string $email): ResponseInterface
    {
        return $this->request('getAccountInfo', [
            'email' => [$email],
        ]);
    }

    public function downloadAccount(int $batchSize = 1000, string $nextPageToken = null): ResponseInterface
    {
        return $this->request('downloadAccount', array_filter([
            'maxResults' => $batchSize,
            'nextPageToken' => $nextPageToken,
        ]));
    }

    public function enableUser($uid): ResponseInterface
    {
        return $this->request('setAccountInfo', [
            'localId' => $uid,
            'disableUser' => false,
        ]);
    }

    public function disableUser($uid): ResponseInterface
    {
        return $this->request('setAccountInfo', [
            'localId' => $uid,
            'disableUser' => true,
        ]);
    }

    public function deleteUser(string $uid): ResponseInterface
    {
        return $this->request('deleteAccount', [
            'localId' => $uid,
        ]);
    }

    public function changeUserPassword(string $uid, string $newPassword): ResponseInterface
    {
        return $this->request('setAccountInfo', [
            'localId' => [$uid],
            'password' => $newPassword,
        ]);
    }

    public function changeUserEmail(string $uid, string $newEmail): ResponseInterface
    {
        return $this->request('setAccountInfo', [
            'localId' => [$uid],
            'email' => $newEmail,
        ]);
    }

    public function getAccountInfo(string $uid): ResponseInterface
    {
        return $this->request('getAccountInfo', [
            'localId' => [$uid],
        ]);
    }

    /**
     * @param string $idToken
     *
     * @return ResponseInterface
     */
    public function sendEmailVerification(string $idToken): ResponseInterface
    {
        return $this->request('getOobConfirmationCode', [
            'requestType' => 'VERIFY_EMAIL',
            'idToken' => $idToken,
        ]);
    }

    public function sendPasswordResetEmail(string $email): ResponseInterface
    {
        return $this->request('getOobConfirmationCode', [
            'email' => $email,
            'requestType' => 'PASSWORD_RESET',
        ]);
    }

    public function revokeRefreshTokens(string $uid): ResponseInterface
    {
        return $this->request('setAccountInfo', [
            'localId' => $uid,
            'validSince' => time(),
        ]);
    }

    private function request(string $uri, $data): ResponseInterface
    {
        try {
            return $this->client->request('POST', $uri, ['json' => $data]);
        } catch (RequestException $e) {
            throw AuthException::fromRequestException($e);
        } catch (\Throwable $e) {
            throw new AuthException($e->getMessage(), $e->getCode(), $e);
        }
    }
}

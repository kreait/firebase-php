<?php

namespace Kreait\Firebase\Auth;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Kreait\Firebase\Exception\Auth\CredentialsMismatch;
use Kreait\Firebase\Exception\Auth\EmailNotFound;
use Kreait\Firebase\Exception\Auth\InvalidCustomToken;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Request\CreateUser;
use Kreait\Firebase\Request\UpdateUser;
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

    public function updateUser(UpdateUser $request): ResponseInterface
    {
        return $this->request('setAccountInfo', $request);
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

    /**
     * Returns a user for the given phone number.
     *
     * @param string $phoneNumber
     *
     * @return ResponseInterface
     */
    public function getUserByPhoneNumber(string $phoneNumber): ResponseInterface
    {
        return $this->request('getAccountInfo', [
            'phoneNumber' => [$phoneNumber],
        ]);
    }

    public function downloadAccount(int $batchSize = 1000, string $nextPageToken = null): ResponseInterface
    {
        return $this->request('downloadAccount', array_filter([
            'maxResults' => $batchSize,
            'nextPageToken' => $nextPageToken,
        ]));
    }

    /**
     * @deprecated 4.2.0
     * @see ApiClient::updateUser()
     */
    public function enableUser($uid): ResponseInterface
    {
        return $this->updateUser(
            UpdateUser::new($uid)->markAsEnabled()
        );
    }

    /**
     * @deprecated 4.2.0
     * @see ApiClient::updateUser()
     */
    public function disableUser($uid): ResponseInterface
    {
        return $this->updateUser(
            UpdateUser::new($uid)->markAsDisabled()
        );
    }

    public function deleteUser(string $uid): ResponseInterface
    {
        return $this->request('deleteAccount', [
            'localId' => $uid,
        ]);
    }

    /**
     * @deprecated 4.2.0
     * @see ApiClient::updateUser()
     */
    public function changeUserPassword(string $uid, string $newPassword): ResponseInterface
    {
        return $this->updateUser(
            UpdateUser::new($uid)->withClearTextPassword($newPassword)
        );
    }

    /**
     * @deprecated 4.2.0
     * @see ApiClient::updateUser()
     */
    public function changeUserEmail(string $uid, string $newEmail): ResponseInterface
    {
        return $this->updateUser(
            UpdateUser::new($uid)->withEmail($newEmail)
        );
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

<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use GuzzleHttp\ClientInterface;
use Kreait\Firebase\Exception\Auth\CredentialsMismatch;
use Kreait\Firebase\Exception\Auth\EmailNotFound;
use Kreait\Firebase\Exception\Auth\InvalidCustomToken;
use Kreait\Firebase\Exception\AuthApiExceptionConverter;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Request;
use Lcobucci\JWT\Token;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * @internal
 */
class ApiClient
{
    /** @var ClientInterface */
    private $client;

    /** @var AuthApiExceptionConverter */
    private $errorHandler;

    /**
     * @internal
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
        $this->errorHandler = new AuthApiExceptionConverter();
    }

    /**
     * Takes a custom token and exchanges it with an ID token.
     *
     * @see https://firebase.google.com/docs/reference/rest/auth/#section-verify-custom-token
     *
     * @throws InvalidCustomToken
     * @throws CredentialsMismatch
     * @throws AuthException
     * @throws FirebaseException
     */
    public function exchangeCustomTokenForIdAndRefreshToken(Token $token): ResponseInterface
    {
        return $this->request('verifyCustomToken', [
            'token' => (string) $token,
            'returnSecureToken' => true,
        ]);
    }

    /**
     * @throws AuthException
     * @throws FirebaseException
     */
    public function createUser(Request\CreateUser $request): ResponseInterface
    {
        return $this->request('signupNewUser', $request);
    }

    /**
     * @throws AuthException
     * @throws FirebaseException
     */
    public function updateUser(Request\UpdateUser $request): ResponseInterface
    {
        return $this->request('setAccountInfo', $request);
    }

    /**
     * @deprecated 4.2.0
     * @see ApiClient::createUser()
     *
     * @codeCoverageIgnore
     *
     * @throws AuthException
     * @throws FirebaseException
     */
    public function signupNewUser(string $email = null, string $password = null): ResponseInterface
    {
        \trigger_error(__METHOD__.' is deprecated.', \E_USER_DEPRECATED);

        return $this->createUser(
            Request\CreateUser::new()
                ->withUnverifiedEmail($email)
                ->withClearTextPassword($password)
        );
    }

    /**
     * Returns a user for the given email address.
     *
     * @throws EmailNotFound
     * @throws AuthException
     * @throws FirebaseException
     */
    public function getUserByEmail(string $email): ResponseInterface
    {
        return $this->request('getAccountInfo', [
            'email' => [$email],
        ]);
    }

    /**
     * @throws AuthException
     * @throws FirebaseException
     */
    public function getUserByPhoneNumber(string $phoneNumber): ResponseInterface
    {
        return $this->request('getAccountInfo', [
            'phoneNumber' => [$phoneNumber],
        ]);
    }

    /**
     * @throws AuthException
     * @throws FirebaseException
     */
    public function downloadAccount(int $batchSize = null, string $nextPageToken = null): ResponseInterface
    {
        $batchSize = $batchSize ?? 1000;

        return $this->request('downloadAccount', \array_filter([
            'maxResults' => $batchSize,
            'nextPageToken' => $nextPageToken,
        ]));
    }

    /**
     * @deprecated 4.2.0
     * @see ApiClient::updateUser()
     *
     * @param mixed $uid
     *
     * @codeCoverageIgnore
     *
     * @throws AuthException
     * @throws FirebaseException
     */
    public function enableUser($uid): ResponseInterface
    {
        \trigger_error(__METHOD__.' is deprecated.', \E_USER_DEPRECATED);

        return $this->updateUser(
            Request\UpdateUser::new()
                ->withUid($uid)
                ->markAsEnabled()
        );
    }

    /**
     * @param mixed $uid
     *
     * @codeCoverageIgnore
     *
     * @deprecated 4.2.0
     * @see ApiClient::updateUser()
     *
     * @throws AuthException
     * @throws FirebaseException
     */
    public function disableUser($uid): ResponseInterface
    {
        \trigger_error(__METHOD__.' is deprecated.', \E_USER_DEPRECATED);

        return $this->updateUser(
            Request\UpdateUser::new()
                ->withUid($uid)
                ->markAsDisabled()
        );
    }

    /**
     * @throws AuthException
     * @throws FirebaseException
     */
    public function deleteUser(string $uid): ResponseInterface
    {
        return $this->request('deleteAccount', [
            'localId' => $uid,
        ]);
    }

    /**
     * @deprecated 4.2.0
     * @see ApiClient::updateUser()
     *
     * @codeCoverageIgnore
     *
     * @throws AuthException
     * @throws FirebaseException
     */
    public function changeUserPassword(string $uid, string $newPassword): ResponseInterface
    {
        \trigger_error(__METHOD__.' is deprecated.', \E_USER_DEPRECATED);

        return $this->updateUser(
            Request\UpdateUser::new()
                ->withUid($uid)
                ->withClearTextPassword($newPassword)
        );
    }

    /**
     * @deprecated 4.2.0
     * @see ApiClient::updateUser()
     *
     * @codeCoverageIgnore
     *
     * @throws AuthException
     * @throws FirebaseException
     */
    public function changeUserEmail(string $uid, string $newEmail): ResponseInterface
    {
        \trigger_error(__METHOD__.' is deprecated.', \E_USER_DEPRECATED);

        return $this->updateUser(
            Request\UpdateUser::new()
                ->withUid($uid)
                ->withEmail($newEmail)
        );
    }

    /**
     * @throws AuthException
     * @throws FirebaseException
     */
    public function getAccountInfo(string $uid): ResponseInterface
    {
        return $this->request('getAccountInfo', [
            'localId' => [$uid],
        ]);
    }

    /**
     * @throws AuthException
     * @throws FirebaseException
     */
    public function verifyPassword(string $email, string $password): ResponseInterface
    {
        return $this->request('verifyPassword', [
            'email' => $email,
            'password' => $password,
        ]);
    }

    /**
     * @throws AuthException
     * @throws FirebaseException
     */
    public function sendEmailVerification(string $idToken, string $continueUrl = null, string $locale = null): ResponseInterface
    {
        $headers = $locale ? ['X-Firebase-Locale' => $locale] : null;

        $data = \array_filter([
            'requestType' => 'VERIFY_EMAIL',
            'idToken' => $idToken,
            'continueUrl' => $continueUrl,
        ]);

        return $this->request('getOobConfirmationCode', $data, $headers);
    }

    /**
     * @throws AuthException
     * @throws FirebaseException
     */
    public function sendPasswordResetEmail(string $email, string $continueUrl = null, string $locale = null): ResponseInterface
    {
        $headers = $locale ? ['X-Firebase-Locale' => $locale] : null;

        $data = \array_filter([
            'email' => $email,
            'requestType' => 'PASSWORD_RESET',
            'continueUrl' => $continueUrl,
        ]);

        return $this->request('getOobConfirmationCode', $data, $headers);
    }

    /**
     * @throws AuthException
     * @throws FirebaseException
     */
    public function revokeRefreshTokens(string $uid): ResponseInterface
    {
        return $this->request('setAccountInfo', [
            'localId' => $uid,
            'validSince' => \time(),
        ]);
    }

    /**
     * @throws AuthException
     * @throws FirebaseException
     */
    public function unlinkProvider(string $uid, array $providers): ResponseInterface
    {
        return $this->request('setAccountInfo', [
            'localId' => $uid,
            'deleteProvider' => $providers,
        ]);
    }

    /**
     * @param mixed $data
     * @param array $headers
     *
     * @throws AuthException
     * @throws FirebaseException
     */
    private function request(string $uri, $data, array $headers = null): ResponseInterface
    {
        if ($data instanceof \JsonSerializable && empty($data->jsonSerialize())) {
            $data = (object) []; // Will be '{}' instead of '[]' when JSON encoded
        }

        $options = \array_filter([
            'json' => $data,
            'headers' => $headers,
        ]);

        try {
            return $this->client->request('POST', $uri, $options);
        } catch (Throwable $e) {
            throw $this->errorHandler->convertException($e);
        }
    }
}

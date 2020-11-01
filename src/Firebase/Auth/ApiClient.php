<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use GuzzleHttp\ClientInterface;
use Kreait\Firebase\Exception\Auth\EmailNotFound;
use Kreait\Firebase\Exception\Auth\ExpiredOobCode;
use Kreait\Firebase\Exception\Auth\InvalidOobCode;
use Kreait\Firebase\Exception\Auth\OperationNotAllowed;
use Kreait\Firebase\Exception\Auth\UserDisabled;
use Kreait\Firebase\Exception\AuthApiExceptionConverter;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Http\WrappedGuzzleClient;
use Kreait\Firebase\Request;
use Kreait\Firebase\Value\Provider;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * @internal
 */
class ApiClient implements ClientInterface
{
    use WrappedGuzzleClient;

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
     * @throws AuthException
     * @throws FirebaseException
     */
    public function createUser(Request\CreateUser $request): ResponseInterface
    {
        return $this->requestApi('signupNewUser', $request->jsonSerialize());
    }

    /**
     * @throws AuthException
     * @throws FirebaseException
     */
    public function updateUser(Request\UpdateUser $request): ResponseInterface
    {
        return $this->requestApi('setAccountInfo', $request->jsonSerialize());
    }

    /**
     * @param array<string, mixed> $claims
     *
     * @throws AuthException
     * @throws FirebaseException
     */
    public function setCustomUserClaims(string $uid, array $claims): ResponseInterface
    {
        return $this->requestApi('https://identitytoolkit.googleapis.com/v1/accounts:update', [
            'localId' => $uid,
            'customAttributes' => \json_encode((object) $claims),
        ]);
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
        return $this->requestApi('getAccountInfo', [
            'email' => [$email],
        ]);
    }

    /**
     * @throws AuthException
     * @throws FirebaseException
     */
    public function getUserByPhoneNumber(string $phoneNumber): ResponseInterface
    {
        return $this->requestApi('getAccountInfo', [
            'phoneNumber' => [$phoneNumber],
        ]);
    }

    /**
     * @throws AuthException
     * @throws FirebaseException
     */
    public function downloadAccount(?int $batchSize = null, ?string $nextPageToken = null): ResponseInterface
    {
        $batchSize = $batchSize ?? 1000;

        return $this->requestApi('downloadAccount', \array_filter([
            'maxResults' => $batchSize,
            'nextPageToken' => $nextPageToken,
        ]));
    }

    /**
     * @throws AuthException
     * @throws FirebaseException
     */
    public function deleteUser(string $uid): ResponseInterface
    {
        return $this->requestApi('deleteAccount', [
            'localId' => $uid,
        ]);
    }

    /**
     * @param string|array<string> $uids
     *
     * @throws AuthException
     * @throws FirebaseException
     */
    public function getAccountInfo($uids): ResponseInterface
    {
        if (!\is_array($uids)) {
            $uids = [$uids];
        }

        return $this->requestApi('getAccountInfo', [
            'localId' => $uids,
        ]);
    }

    /**
     * @throws ExpiredOobCode
     * @throws InvalidOobCode
     * @throws OperationNotAllowed
     * @throws AuthException
     * @throws FirebaseException
     */
    public function verifyPasswordResetCode(string $oobCode): ResponseInterface
    {
        return $this->requestApi('resetPassword', [
            'oobCode' => $oobCode,
        ]);
    }

    /**
     * @throws ExpiredOobCode
     * @throws InvalidOobCode
     * @throws OperationNotAllowed
     * @throws UserDisabled
     * @throws AuthException
     * @throws FirebaseException
     */
    public function confirmPasswordReset(string $oobCode, string $newPassword): ResponseInterface
    {
        return $this->requestApi('resetPassword', [
            'oobCode' => $oobCode,
            'newPassword' => $newPassword,
        ]);
    }

    /**
     * @throws AuthException
     * @throws FirebaseException
     */
    public function revokeRefreshTokens(string $uid): ResponseInterface
    {
        return $this->requestApi('setAccountInfo', [
            'localId' => $uid,
            'validSince' => \time(),
        ]);
    }

    /**
     * @param array<int, string|Provider> $providers
     *
     * @throws AuthException
     * @throws FirebaseException
     */
    public function unlinkProvider(string $uid, array $providers): ResponseInterface
    {
        return $this->requestApi('setAccountInfo', [
            'localId' => $uid,
            'deleteProvider' => $providers,
        ]);
    }

    /**
     * @param array<mixed> $data
     *
     * @throws AuthException
     * @throws FirebaseException
     */
    private function requestApi(string $uri, array $data): ResponseInterface
    {
        $options = [];

        if (!empty($data)) {
            $options['json'] = $data;
        }

        try {
            return $this->request('POST', $uri, $options);
        } catch (Throwable $e) {
            throw $this->errorHandler->convertException($e);
        }
    }
}

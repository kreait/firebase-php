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
use Kreait\Firebase\Project\ProjectId;
use Kreait\Firebase\Request;
use Kreait\Firebase\Util\JSON;
use Kreait\Firebase\Value\Provider;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * @internal
 */
class ApiClient
{
    private ClientInterface $client;
    private ?TenantId $tenantId;

    private AuthApiExceptionConverter $errorHandler;

    /**
     * @internal
     */
    public function __construct(ClientInterface $client, ?TenantId $tenantId = null)
    {
        $this->client = $client;
        $this->tenantId = $tenantId;
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
            'customAttributes' => JSON::encode($claims, JSON_FORCE_OBJECT),
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
    public function importUsers(array $users, array $options, ProjectId $projectId): ResponseInterface
    {
        $body = [];
        
        $body['users'] = [];

        $body['hashAlgorithm'] = $options['hash']['algorithm'] ?? null;
        $body['rounds'] = $options['hash']['rounds'] ?? null;
        $body['signerKey'] = $options['hash']['key'] ?? null;
        $body['cpuMemCost'] = $options['hash']['memoryCost'] ?? null;
        $body['parallelization'] = $options['hash']['parallelization'] ?? null;
        $body['blockSize'] = $options['hash']['blockSize'] ?? null;
        $body['dkLen'] = $options['hash']['derivedKeyLength'] ?? null;
        $body['saltSeparator'] = $options['hash']['saltSeparator'] ?? null;

        foreach ($users as $userData) {
            $userData['localId'] = $userData['uid'] ?? null;
            $userData['salt'] = $userData['passwordSalt'] ?? null;
            unset($userData['uid'], $userData['passwordSalt']);

            $body['users'][] = $userData;
        }

        return $this->requestApi(
            "https://identitytoolkit.googleapis.com/v1/projects/{$projectId->value()}/accounts:batchCreate",
            \array_filter($body, static function ($value) {
                return $value !== null;
            })
        );
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
     * @throws AuthException
     * @throws FirebaseException
     */
    public function deleteUsers(array $options, ProjectId $projectId): ResponseInterface
    {
        return $this->requestApi(
            "https://identitytoolkit.googleapis.com/v1/projects/{$projectId->value()}/accounts:batchDelete",
            \array_filter($options, static function ($value) {
                return $value !== null;
            })
        );
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

        if ($this->tenantId) {
            $data['tenantId'] = $this->tenantId->toString();
        }

        if (!empty($data)) {
            $options['json'] = $data;
        }

        try {
            return $this->client->request('POST', $uri, $options);
        } catch (Throwable $e) {
            throw $this->errorHandler->convertException($e);
        }
    }
}

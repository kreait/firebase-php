<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Query;
use GuzzleHttp\Psr7\Utils;
use Kreait\Firebase\Exception\Auth\AuthError;
use Kreait\Firebase\Exception\Auth\EmailNotFound;
use Kreait\Firebase\Exception\Auth\ExpiredOobCode;
use Kreait\Firebase\Exception\Auth\InvalidOobCode;
use Kreait\Firebase\Exception\Auth\OperationNotAllowed;
use Kreait\Firebase\Exception\Auth\UserDisabled;
use Kreait\Firebase\Exception\AuthApiExceptionConverter;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Project\ProjectId;
use Kreait\Firebase\Request;
use Kreait\Firebase\Util\JSON;
use Kreait\Firebase\Value\Provider;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * @internal
 */
class ApiClient
{
    private const LEGACY_URL = 'https://www.googleapis.com/identitytoolkit/v3/relyingparty';

    private const PROJECTLESS_URL_FORMAT = 'https://identitytoolkit.googleapis.com/{version}{api}';

    private const URL_FORMAT = 'https://identitytoolkit.googleapis.com/{version}/projects/{projectId}{api}';
    private const TENANT_URL_FORMAT = 'https://identitytoolkit.googleapis.com/{version}/projects/{projectId}/tenants/{tenantId}{api}';

    private const EMULATOR_URL_FORMAT = 'http://{host}/identitytoolkit.googleapis.com/{version}/projects/{projectId}{api}';
    private const EMULATOR_TENANT_URL_FORMAT = 'http://{host}/identitytoolkit.googleapis.com/{version}/projects/{projectId}/tenants/{tenantId}{api}';

    private ClientInterface $client;
    private ?TenantId $tenantId;

    private AuthApiExceptionConverter $errorHandler;
    private ?ProjectId $projectId;
    private string $baseUrlFormat;

    /**
     * @internal
     */
    public function __construct(
        ClientInterface $client,
        ?TenantId $tenantId = null,
        ?ProjectId $projectId = null,
        ?string $emulatorHost = null
    ) {
        $this->client = $client;
        $this->tenantId = $tenantId;
        $this->projectId = $projectId;

        $this->baseUrlFormat = $this->determineBaseUrl($projectId, $tenantId, $emulatorHost);
        $this->errorHandler = new AuthApiExceptionConverter();
    }

    private function determineBaseUrl(?ProjectId $projectId, ?TenantId $tenantId, ?string $emulatorHost): string
    {
        if ($projectId && $emulatorHost && $tenantId) {
            $urlFormat = self::EMULATOR_TENANT_URL_FORMAT;
        } elseif ($projectId && $emulatorHost) {
            $urlFormat = self::EMULATOR_URL_FORMAT;
        } elseif ($projectId && $tenantId) {
            $urlFormat = self::TENANT_URL_FORMAT;
        } elseif ($projectId !== null) {
            $urlFormat = self::URL_FORMAT;
        } else {
            $urlFormat = self::PROJECTLESS_URL_FORMAT;
        }

        return \strtr($urlFormat, [
            '{host}' => $emulatorHost ?: '',
            '{projectId}' => $projectId !== null ? $projectId->value() : '',
            '{tenantId}' => $tenantId !== null ? $tenantId->toString() : '',
        ]);
    }

    /**
     * @throws AuthException
     */
    public function createUser(Request\CreateUser $request): ResponseInterface
    {
        $params = $request->jsonSerialize();

        if ($this->projectId !== null) {
            $apiRequest = $this->createRequest('POST', '/accounts');
        } else {
            $apiRequest = $this->createProjectLessRequest('POST', '/accounts:signUp');
            if ($this->tenantId !== null) {
                $params['tenantId'] = $this->tenantId->toString();
            }
        }

        $apiRequest = $apiRequest
            ->withBody(Utils::streamFor(JSON::encode((object) $params, JSON_FORCE_OBJECT)))
        ;

        return $this->request($apiRequest);
    }

    /**
     * @throws AuthException
     */
    public function updateUser(Request\UpdateUser $request): ResponseInterface
    {
        $apiRequest = $this->createRequest('POST', '/accounts:update')
            ->withBody(Utils::streamFor(JSON::encode((object) $request->jsonSerialize())))
        ;

        return $this->request($apiRequest);
    }

    /**
     * @param array<string, mixed> $claims
     *
     * @throws AuthException
     */
    public function setCustomUserClaims(string $uid, array $claims): ResponseInterface
    {
        $json = JSON::encode([
            'localId' => $uid,
            'customAttributes' => JSON::encode($claims, JSON_FORCE_OBJECT),
        ]);

        $apiRequest = $this->createRequest('POST', '/accounts:update')
            ->withBody(Utils::streamFor($json))
        ;

        return $this->request($apiRequest);
    }

    /**
     * Returns a user for the given email address.
     *
     * @throws EmailNotFound
     * @throws AuthException
     */
    public function getUserByEmail(string $email): ResponseInterface
    {
        $apiRequest = $this->createRequest('POST', '/accounts:lookup');

        $data = JSON::encode([
            'email' => [$email],
        ]);

        $apiRequest = $apiRequest->withBody(Utils::streamFor($data));

        return $this->request($apiRequest);
    }

    /**
     * @throws AuthException
     */
    public function getUserByPhoneNumber(string $phoneNumber): ResponseInterface
    {
        $apiRequest = $this->createRequest('POST', '/accounts:lookup');

        $data = JSON::encode([
            'phoneNumber' => [$phoneNumber],
        ]);

        $apiRequest = $apiRequest->withBody(Utils::streamFor($data));

        return $this->request($apiRequest);
    }

    /**
     * @throws AuthException
     */
    public function downloadAccount(?int $batchSize = null, ?string $nextPageToken = null): ResponseInterface
    {
        $batchSize ??= 1000;

        $params = [
            'maxResults' => $batchSize,
            'nextPageToken' => $nextPageToken,
        ];

        if (!$this->projectId instanceof \Kreait\Firebase\Project\ProjectId) {
            return $this->requestApi(self::LEGACY_URL.'/downloadAccount', \array_filter([
                'maxResults' => $batchSize,
                'nextPageToken' => $nextPageToken,
            ]));
        }

        $apiRequest = $this->createRequest('GET', '/accounts:batchGet?'.Query::build($params));

        return $this->request($apiRequest);
    }

    /**
     * @throws AuthException
     */
    public function deleteUser(string $uid): ResponseInterface
    {
        $params = ['localId' => $uid];

        if ($this->projectId !== null) {
            $apiRequest = $this->createRequest('POST', '/accounts:delete');
        } else {
            if ($this->tenantId !== null) {
                $params['tenantId'] = $this->tenantId->toString();
            }

            $apiRequest = $this->createProjectLessRequest('POST', '/accounts:delete');
        }

        $params = JSON::encode($params);

        $apiRequest = $apiRequest->withBody(Utils::streamFor($params));

        return $this->request($apiRequest);
    }

    /**
     * @param string[] $uids
     *
     * @throws AuthException
     */
    public function deleteUsers(array $uids, bool $forceDeleteEnabledUsers): ResponseInterface
    {
        if (!$this->projectId instanceof \Kreait\Firebase\Project\ProjectId) {
            throw AuthError::missingProjectId('Batch user deletion cannot be performed.');
        }

        $params = JSON::encode([
            'localIds' => $uids,
            'force' => $forceDeleteEnabledUsers,
        ]);

        return $this->request(
            $this->createRequest('POST', '/accounts:batchDelete')->withBody(Utils::streamFor($params))
        );
    }

    /**
     * @param string|array<string> $uids
     *
     * @throws AuthException
     */
    public function getAccountInfo($uids): ResponseInterface
    {
        $params = [
            'localId' => \is_array($uids) ? $uids : [$uids],
        ];

        if ($this->projectId !== null) {
            $apiRequest = $this->createRequest('POST', '/accounts:lookup');
        } else {
            return $this->requestApi(self::LEGACY_URL.'/getAccountInfo', $params);
        }

        $data = JSON::encode($params);

        $apiRequest = $apiRequest->withBody(Utils::streamFor($data));

        return $this->request($apiRequest);
    }

    /**
     * @throws ExpiredOobCode
     * @throws InvalidOobCode
     * @throws OperationNotAllowed
     * @throws AuthException
     */
    public function verifyPasswordResetCode(string $oobCode): ResponseInterface
    {
        $params = JSON::encode(['oobCode' => $oobCode]);

        $apiRequest = $this->createProjectLessRequest('POST', '/accounts:resetPassword')
            ->withBody(Utils::streamFor($params))
        ;

        return $this->request($apiRequest);
    }

    /**
     * @throws ExpiredOobCode
     * @throws InvalidOobCode
     * @throws OperationNotAllowed
     * @throws UserDisabled
     * @throws AuthException
     */
    public function confirmPasswordReset(string $oobCode, string $newPassword): ResponseInterface
    {
        $params = JSON::encode([
            'oobCode' => $oobCode,
            'newPassword' => $newPassword,
        ]);

        $apiRequest = $this->createProjectLessRequest('POST', '/accounts:resetPassword')
            ->withBody(Utils::streamFor($params))
        ;

        return $this->request($apiRequest);
    }

    /**
     * @throws AuthException
     */
    public function revokeRefreshTokens(string $uid): ResponseInterface
    {
        $params = JSON::encode([
            'localId' => $uid,
            'validSince' => \time(),
        ]);

        $apiRequest = $this->createRequest('POST', '/accounts:update')
            ->withBody(Utils::streamFor($params))
        ;

        return $this->request($apiRequest);
    }

    /**
     * @param array<int, string|Provider> $providers
     *
     * @throws AuthException
     */
    public function unlinkProvider(string $uid, array $providers): ResponseInterface
    {
        return $this->requestApi(self::PROJECTLESS_URL_FORMAT.'/accounts:update', [
            'localId' => $uid,
            'deleteProvider' => $providers,
        ]);
    }

    public function signInWithCustomToken(string $customToken): ResponseInterface
    {
        return $this->requestApi(self::PROJECTLESS_URL_FORMAT.'/accounts:signInWithCustomToken', [
            'token' => $customToken,
        ]);
    }

    /**
     * @throws AuthException
     */
    public function sendActionLink(SendActionLink $action): ResponseInterface
    {
        $params = [
            'requestType' => $action->type(),
            'email' => $action->email(),
        ] + $action->settings()->toArray();

        if (!$this->projectId && $this->tenantId) {
            $params['tenantId'] = $this->tenantId->toString();
        }

        if ($idTokenString = $action->idTokenString()) {
            $params['idToken'] = $idTokenString;
        }

        $request = $this->createRequest('POST', '/accounts:sendOobCode')
            ->withBody(Utils::streamFor(JSON::encode($params)))
        ;

        if ($locale = $action->locale()) {
            $request = $request->withHeader('X-Firebase-Locale', $locale);
        }

        return $this->request($request);
    }

    /**
     * @param array<mixed> $data
     *
     * @throws AuthException
     */
    private function requestApi(string $uri, array $data): ResponseInterface
    {
        $options = [];
        $tenantId = $data['tenantId'] ?? $this->tenantId ?? null;
        $tenantId = $tenantId instanceof TenantId ? $tenantId->toString() : $tenantId;

        if ($tenantId) {
            $data['tenantId'] = $tenantId;
        }

        if (!empty($data)) {
            $options['json'] = $data;
        }

        return $this->request(new \GuzzleHttp\Psr7\Request('POST', $uri), $options);
    }

    public function createRequest(string $method, string $api, ?string $version = null): RequestInterface
    {
        $url = \strtr($this->baseUrlFormat, [
            '{version}' => $version ?? 'v1',
            '{api}' => $api,
        ]);

        return (new \GuzzleHttp\Psr7\Request($method, $url))
            ->withHeader('Accept', 'application/json')
            ->withHeader('Content-Type', 'application/json; charset=UTF-8')
        ;
    }

    public function createProjectLessRequest(string $method, string $api, ?string $version = null): RequestInterface
    {
        $url = \strtr(self::PROJECTLESS_URL_FORMAT, [
            '{version}' => $version ?? 'v1',
            '{api}' => $api,
        ]);

        return (new \GuzzleHttp\Psr7\Request($method, $url))
            ->withHeader('Accept', 'application/json')
            ->withHeader('Content-Type', 'application/json; charset=UTF-8')
        ;
    }

    /**
     * @param array<string, mixed>|null $options
     *
     * @throws AuthException
     */
    public function request(RequestInterface $request, ?array $options = null): ResponseInterface
    {
        $options ??= [];

        try {
            return $this->client->send($request, $options);
        } catch (Throwable $e) {
            throw $this->errorHandler->convertException($e);
        }
    }
}

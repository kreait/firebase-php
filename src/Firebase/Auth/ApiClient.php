<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use Beste\Json;
use GuzzleHttp\ClientInterface;
use Kreait\Firebase\Auth\SignIn\Handler as SignInHandler;
use Kreait\Firebase\Exception\Auth\EmailNotFound;
use Kreait\Firebase\Exception\Auth\ExpiredOobCode;
use Kreait\Firebase\Exception\Auth\InvalidOobCode;
use Kreait\Firebase\Exception\Auth\OperationNotAllowed;
use Kreait\Firebase\Exception\Auth\UserDisabled;
use Kreait\Firebase\Exception\AuthApiExceptionConverter;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Request;
use Psr\Clock\ClockInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * @internal
 */
class ApiClient
{
    private const PROJECT_URL_FORMAT = 'https://identitytoolkit.googleapis.com/{version}/projects/{projectId}{api}';
    private const TENANT_URL_FORMAT = 'https://identitytoolkit.googleapis.com/{version}/projects/{projectId}/tenants/{tenantId}{api}';

    private string $projectId;
    private ?string $tenantId;
    private ClientInterface $client;
    private SignInHandler $signInHandler;
    private ClockInterface $clock;

    private AuthApiExceptionConverter $errorHandler;

    public function __construct(string $projectId, ?string $tenantId, ClientInterface $client, SignInHandler $signInHandler, ClockInterface $clock)
    {
        $this->projectId = $projectId;
        $this->tenantId = $tenantId;
        $this->client = $client;
        $this->signInHandler = $signInHandler;
        $this->clock = $clock;
        $this->errorHandler = new AuthApiExceptionConverter();
    }

    /**
     * @throws AuthException
     */
    public function createUser(Request\CreateUser $request): ResponseInterface
    {
        return $this->requestApi('https://identitytoolkit.googleapis.com/v1/accounts:signUp', $request->jsonSerialize());
    }

    /**
     * @throws AuthException
     */
    public function updateUser(Request\UpdateUser $request): ResponseInterface
    {
        return $this->requestApi(
            $this->createUrl('/accounts:update'),
            $request->jsonSerialize()
        );
    }

    /**
     * @param array<string, mixed> $claims
     *
     * @throws AuthException
     */
    public function setCustomUserClaims(string $uid, array $claims): ResponseInterface
    {
        return $this->requestApi(
            $this->createUrl('/accounts:update'),
            [
                'localId' => $uid,
                'customAttributes' => Json::encode($claims, JSON_FORCE_OBJECT),
            ]
        );
    }

    /**
     * Returns a user for the given email address.
     *
     * @throws EmailNotFound
     * @throws AuthException
     */
    public function getUserByEmail(string $email): ResponseInterface
    {
        return $this->requestApi(
            $this->createUrl('/accounts:lookup'),
            [
                'email' => [$email],
            ]
        );
    }

    /**
     * @throws AuthException
     */
    public function getUserByPhoneNumber(string $phoneNumber): ResponseInterface
    {
        return $this->requestApi(
            $this->createUrl('/accounts:lookup'),
            [
                'phoneNumber' => [$phoneNumber],
            ]
        );
    }

    /**
     * @throws AuthException
     */
    public function downloadAccount(?int $batchSize = null, ?string $nextPageToken = null): ResponseInterface
    {
        $batchSize = $batchSize ?: 1000;

        $urlParams = \array_filter([
            'maxResults' => (string) $batchSize,
            'nextPageToken' => (string) $nextPageToken,
        ]);

        $query = \http_build_query($urlParams);

        return $this->requestApi(
            $this->createUrl('/accounts:batchGet?'.$query),
            [],
            'GET'
        );
    }

    /**
     * @throws AuthException
     */
    public function deleteUser(string $uid): ResponseInterface
    {
        return $this->requestApi(
            $this->createUrl('/accounts:delete'),
            [
                'localId' => $uid,
            ]
        );
    }

    /**
     * @param string[] $uids
     *
     * @throws AuthException
     */
    public function deleteUsers(array $uids, bool $forceDeleteEnabledUsers): ResponseInterface
    {
        $data = [
            'localIds' => $uids,
            'force' => $forceDeleteEnabledUsers,
        ];

        return $this->requestApi($this->createUrl('/accounts:batchDelete'), $data);
    }

    /**
     * @param string|array<string> $uids
     *
     * @throws AuthException
     */
    public function getAccountInfo($uids): ResponseInterface
    {
        if (!\is_array($uids)) {
            $uids = [$uids];
        }

        return $this->requestApi($this->createUrl('/accounts:lookup'), ['localId' => $uids]);
    }

    /**
     * @throws ExpiredOobCode
     * @throws InvalidOobCode
     * @throws OperationNotAllowed
     * @throws AuthException
     */
    public function verifyPasswordResetCode(string $oobCode): ResponseInterface
    {
        return $this->requestApi(
            'https://identitytoolkit.googleapis.com/v1/accounts:resetPassword',
            ['oobCode' => $oobCode]
        );
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
        return $this->requestApi(
            'https://identitytoolkit.googleapis.com/v1/accounts:resetPassword',
            [
                'oobCode' => $oobCode,
                'newPassword' => $newPassword,
            ]
        );
    }

    /**
     * @throws AuthException
     */
    public function revokeRefreshTokens(string $uid): ResponseInterface
    {
        return $this->requestApi($this->createUrl('/accounts:update'), [
            'localId' => $uid,
            'validSince' => (string) \time(),
        ]);
    }

    /**
     * @param array<int, \Stringable|string> $providers
     *
     * @throws AuthException
     */
    public function unlinkProvider(string $uid, array $providers): ResponseInterface
    {
        $providers = \array_map('strval', $providers);

        return $this->requestApi($this->createUrl('/accounts:update'), [
            'localId' => $uid,
            'deleteProvider' => $providers,
        ]);
    }

    /**
     * @param int|\DateInterval $ttl
     */
    public function createSessionCookie(string $idToken, $ttl): string
    {
        return (new CreateSessionCookie\GuzzleApiClientHandler($this->client, $this->projectId))
            ->handle(CreateSessionCookie::forIdToken($idToken, $this->tenantId, $ttl, $this->clock))
        ;
    }

    public function getEmailActionLink(string $type, string $email, ActionCodeSettings $actionCodeSettings, ?string $locale = null): string
    {
        return (new CreateActionLink\GuzzleApiClientHandler($this->client, $this->projectId))
            ->handle(CreateActionLink::new($type, $email, $actionCodeSettings, $this->tenantId, $locale))
            ;
    }

    public function sendEmailActionLink(string $type, string $email, ActionCodeSettings $actionCodeSettings, ?string $locale = null, ?string $idToken = null): void
    {
        $createAction = CreateActionLink::new($type, $email, $actionCodeSettings, $this->tenantId, $locale);
        $sendAction = new SendActionLink($createAction, $locale);

        if ($idToken !== null) {
            $sendAction = $sendAction->withIdTokenString($idToken);
        }

        (new SendActionLink\GuzzleApiClientHandler($this->client, $this->projectId))->handle($sendAction);
    }

    public function handleSignIn(SignIn $action): SignInResult
    {
        if ($this->tenantId !== null) {
            $action = $action->withTenantId($this->tenantId);
        }

        return $this->signInHandler->handle($action);
    }

    /**
     * @param array<mixed> $data
     *
     * @throws AuthException
     */
    private function requestApi(string $uri, array $data, string $method = 'POST'): ResponseInterface
    {
        $options = [];

        if (!\str_contains($uri, 'projects')) {
            $data['targetProjectId'] = $this->projectId;
        }

        if ($this->tenantId !== null && !\str_contains($uri, 'tenants')) {
            $data['tenantId'] = $this->tenantId;
        }

        if (!empty($data)) {
            if ($method === 'POST') {
                $options['json'] = $data;
            } else {
                $options['query'] = $data;
            }
        }

        try {
            return $this->client->request($method, $uri, $options);
        } catch (Throwable $e) {
            throw $this->errorHandler->convertException($e);
        }
    }

    private function createUrl(?string $api): string
    {
        $baseUrl = self::PROJECT_URL_FORMAT;
        $pathParams = [
            '{api}' => $api ?? '',
            '{projectId}' => $this->projectId,
            '{version}' => 'v1',
        ];

        if ($this->tenantId !== null) {
            $baseUrl = self::TENANT_URL_FORMAT;
            $pathParams['{tenantId}'] = $this->tenantId;
        }

        return \strtr($baseUrl, $pathParams);
    }
}

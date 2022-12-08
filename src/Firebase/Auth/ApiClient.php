<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use Beste\Json;
use DateInterval;
use GuzzleHttp\ClientInterface;
use Kreait\Firebase\Auth\CreateSessionCookie\GuzzleApiClientHandler;
use Kreait\Firebase\Auth\SignIn\Handler as SignInHandler;
use Kreait\Firebase\Exception\Auth\EmailNotFound;
use Kreait\Firebase\Exception\Auth\ExpiredOobCode;
use Kreait\Firebase\Exception\Auth\InvalidOobCode;
use Kreait\Firebase\Exception\Auth\OperationNotAllowed;
use Kreait\Firebase\Exception\Auth\UserDisabled;
use Kreait\Firebase\Exception\AuthApiExceptionConverter;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Request\CreateUser;
use Kreait\Firebase\Request\UpdateUser;
use Psr\Clock\ClockInterface;
use Psr\Http\Message\ResponseInterface;
use Stringable;
use Throwable;

use function array_filter;
use function array_map;
use function is_array;
use function str_contains;
use function time;

/**
 * @internal
 */
class ApiClient
{
    private readonly ProjectAwareAuthResourceUrlBuilder|TenantAwareAuthResourceUrlBuilder $awareAuthResourceUrlBuilder;
    private readonly AuthResourceUrlBuilder $authResourceUrlBuilder;
    private readonly AuthApiExceptionConverter $errorHandler;

    /**
     * @param non-empty-string $projectId
     * @param non-empty-string|null $tenantId
     */
    public function __construct(
        private readonly string $projectId,
        private readonly ?string $tenantId,
        private readonly ClientInterface $client,
        private readonly SignInHandler $signInHandler,
        private readonly ClockInterface $clock,
    ) {
        $this->errorHandler = new AuthApiExceptionConverter();

        $this->awareAuthResourceUrlBuilder = $tenantId !== null
            ? TenantAwareAuthResourceUrlBuilder::forProjectAndTenant($projectId, $tenantId)
            : ProjectAwareAuthResourceUrlBuilder::forProject($projectId);

        $this->authResourceUrlBuilder = AuthResourceUrlBuilder::create();
    }

    /**
     * @throws AuthException
     */
    public function createUser(CreateUser $request): ResponseInterface
    {
        $url = $this->authResourceUrlBuilder->getUrl('/accounts:signUp');

        return $this->requestApi($url, Json::decode(Json::encode($request), true));
    }

    /**
     * @throws AuthException
     */
    public function updateUser(UpdateUser $request): ResponseInterface
    {
        $url = $this->awareAuthResourceUrlBuilder->getUrl('/accounts:update');

        return $this->requestApi($url, Json::decode(Json::encode($request), true));
    }

    /**
     * @param array<non-empty-string, mixed> $claims
     *
     * @throws AuthException
     */
    public function setCustomUserClaims(string $uid, array $claims): ResponseInterface
    {
        $url = $this->awareAuthResourceUrlBuilder->getUrl('/accounts:update');

        return $this->requestApi($url, [
            'localId' => $uid,
            'customAttributes' => JSON::encode((object) $claims),
        ]);
    }

    /**
     * Returns a user for the given email address.
     *
     * @throws AuthException
     * @throws EmailNotFound
     */
    public function getUserByEmail(string $email): ResponseInterface
    {
        $url = $this->awareAuthResourceUrlBuilder->getUrl('/accounts:lookup');

        return $this->requestApi($url, ['email' => [$email]]);
    }

    /**
     * @throws AuthException
     */
    public function getUserByPhoneNumber(string $phoneNumber): ResponseInterface
    {
        $url = $this->awareAuthResourceUrlBuilder->getUrl('/accounts:lookup');

        return $this->requestApi($url, ['phoneNumber' => [$phoneNumber]]);
    }

    /**
     * @throws AuthException
     */
    public function downloadAccount(?int $batchSize = null, ?string $nextPageToken = null): ResponseInterface
    {
        $batchSize = $batchSize ?: 1000;

        $urlParams = array_filter([
            'maxResults' => (string) $batchSize,
            'nextPageToken' => (string) $nextPageToken,
        ]);

        $url = $this->awareAuthResourceUrlBuilder->getUrl('/accounts:batchGet', $urlParams);

        return $this->requestApi($url);
    }

    /**
     * @throws AuthException
     */
    public function deleteUser(string $uid): ResponseInterface
    {
        $url = $this->awareAuthResourceUrlBuilder->getUrl('/accounts:delete');

        return $this->requestApi($url, ['localId' => $uid]);
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

        $url = $this->awareAuthResourceUrlBuilder->getUrl('/accounts:batchDelete');

        return $this->requestApi($url, $data);
    }

    /**
     * @param string|list<non-empty-string> $uids
     *
     * @throws AuthException
     */
    public function getAccountInfo(string|array $uids): ResponseInterface
    {
        if (!is_array($uids)) {
            $uids = [$uids];
        }

        $url = $this->awareAuthResourceUrlBuilder->getUrl('/accounts:lookup');

        return $this->requestApi($url, ['localId' => $uids]);
    }

    /**
     * @throws AuthException
     */
    public function queryUsers(UserQuery $query): ResponseInterface
    {
        $url = $this->awareAuthResourceUrlBuilder->getUrl('/accounts:query');

        return $this->requestApi($url, Json::decode(Json::encode($query), true));
    }

    /**
     * @throws AuthException
     * @throws ExpiredOobCode
     * @throws InvalidOobCode
     * @throws OperationNotAllowed
     */
    public function verifyPasswordResetCode(string $oobCode): ResponseInterface
    {
        $url = $this->authResourceUrlBuilder->getUrl('/accounts:resetPassword');

        return $this->requestApi($url, ['oobCode' => $oobCode]);
    }

    /**
     * @throws AuthException
     * @throws ExpiredOobCode
     * @throws InvalidOobCode
     * @throws OperationNotAllowed
     * @throws UserDisabled
     */
    public function confirmPasswordReset(string $oobCode, string $newPassword): ResponseInterface
    {
        $url = $this->authResourceUrlBuilder->getUrl('/accounts:resetPassword');

        return $this->requestApi($url, [
            'oobCode' => $oobCode,
            'newPassword' => $newPassword,
        ]);
    }

    /**
     * @throws AuthException
     */
    public function revokeRefreshTokens(string $uid): ResponseInterface
    {
        $url = $this->awareAuthResourceUrlBuilder->getUrl('/accounts:update');

        return $this->requestApi($url, [
            'localId' => $uid,
            'validSince' => (string) time(),
        ]);
    }

    /**
     * @param list<Stringable|non-empty-string> $providers
     *
     * @throws AuthException
     */
    public function unlinkProvider(string $uid, array $providers): ResponseInterface
    {
        $url = $this->awareAuthResourceUrlBuilder->getUrl('/accounts:update');
        $providers = array_map('strval', $providers);

        return $this->requestApi($url, [
            'localId' => $uid,
            'deleteProvider' => $providers,
        ]);
    }

    public function createSessionCookie(string $idToken, int|DateInterval $ttl): string
    {
        return (new GuzzleApiClientHandler($this->client, $this->projectId))
            ->handle(CreateSessionCookie::forIdToken($idToken, $this->tenantId, $ttl, $this->clock));
    }

    public function getEmailActionLink(string $type, string $email, ActionCodeSettings $actionCodeSettings, ?string $locale = null): string
    {
        return (new CreateActionLink\GuzzleApiClientHandler($this->client, $this->projectId))
            ->handle(CreateActionLink::new($type, $email, $actionCodeSettings, $this->tenantId, $locale));
    }

    /**
     * TODO: Make that this method can be emulated.
     */
    public function sendEmailActionLink(string $type, string $email, ActionCodeSettings $actionCodeSettings, ?string $locale = null, ?string $idToken = null): void
    {
        $createAction = CreateActionLink::new($type, $email, $actionCodeSettings, $this->tenantId, $locale);
        $sendAction = new SendActionLink($createAction, $locale);

        if ($idToken !== null) {
            $sendAction = $sendAction->withIdTokenString($idToken);
        }

        (new SendActionLink\GuzzleApiClientHandler($this->client, $this->projectId))->handle($sendAction);
    }

    /**
     * TODO: Make that this method can be emulated.
     */
    public function handleSignIn(SignIn $action): SignInResult
    {
        if ($this->tenantId !== null) {
            $action = $action->withTenantId($this->tenantId);
        }

        return $this->signInHandler->handle($action);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws AuthException
     */
    private function requestApi(string $uri, ?array $data = null): ResponseInterface
    {
        $options = [];
        $method = 'GET';

        if (!str_contains($uri, 'projects')) {
            $data['targetProjectId'] = $this->projectId;
        }

        if ($this->tenantId !== null && !str_contains($uri, 'tenants')) {
            $data['tenantId'] = $this->tenantId;
        }

        if (!empty($data)) {
            $method = 'POST';
            $options['json'] = $data;
        }

        try {
            return $this->client->request($method, $uri, $options);
        } catch (Throwable $e) {
            throw $this->errorHandler->convertException($e);
        }
    }
}

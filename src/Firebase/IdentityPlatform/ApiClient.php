<?php

declare(strict_types=1);

namespace Kreait\Firebase\IdentityPlatform;

use Kreait\Firebase\Auth\TenantId;

use GuzzleHttp\ClientInterface;

use Kreait\Firebase\Exception\IdentityPlatformApiExceptionConverter;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\IdentityPlatform\IdentityPlatformError;
use Kreait\Firebase\IdentityPlatform;
use Kreait\Firebase\Project\ProjectId;
use Kreait\Firebase\Request\DefaultSupportedIdpConfig as DefaultSupportedIdpConfigRequest;
use Kreait\Firebase\Request\InboundSamlConfig as InboundSamlConfigRequest;
use Kreait\Firebase\Request\OAuthIdpConfig as OAuthIdpConfigRequest;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * @internal
 */
class ApiClient
{
    private ClientInterface $client;
    private ?TenantId $tenantId;
    private ?ProjectId $projectId;
    private IdentityPlatformApiExceptionConverter $errorHandler;

    /**
     * @internal
     */
    public function __construct(ClientInterface $client, ?ProjectId $projectId, ?TenantId $tenantId = null)
    {
        $this->client = $client;
        $this->projectId = $projectId;
        $this->tenantId = $tenantId;
        $this->errorHandler = new IdentityPlatformApiExceptionConverter();
    }

    /**
     * @throws AuthException
     * @throws FirebaseException
     */
    public function listAllDefaultSupportedIdpConfigs() : ResponseInterface
    {
        return $this->requestApi('GET', 'defaultSupportedIdps', []);
    }

    /**
     * @throws AuthException
     * @throws FirebaseException
     */
    public function listDefaultSupportedIdpConfigs() : ResponseInterface
    {
        $uri = 'defaultSupportedIdpConfigs';
        return $this->requestApiWithProjectId($uri, [], 'GET');
    }

    /**
     * @param DefaultSupportedIdpConfigRequest $request
     * @throws AuthException
     * @throws FirebaseException
     */
    public function createDefaultSupportedIdpConfigs(DefaultSupportedIdpConfigRequest $request) : ResponseInterface
    {
        $uri = 'defaultSupportedIdpConfigs';
        $query = ['idpId' => $request->getName()];
        return $this->requestApiWithProjectId($uri, $request->jsonSerialize(), 'POST', $query);
    }

    /**
     * @param string $name
     * @throws AuthException
     * @throws FirebaseException
     */
    public function deleteDefaultSupportedIdpConfigs(string $name) : ResponseInterface
    {
        $uri = sprintf('defaultSupportedIdpConfigs/%s', $name);
        return $this->requestApiWithProjectId($uri, [], 'DELETE');
    }

    /**
     * @param string $name
     * @throws AuthException
     * @throws FirebaseException
     */
    public function getDefaultSupportedIdpConfigs(string $name) : ResponseInterface
    {
        $uri = sprintf('defaultSupportedIdpConfigs/%s', $name);
        return $this->requestApiWithProjectId($uri, [], 'GET');
    }

    /**
     * @param string $name
     * @param DefaultSupportedIdpConfigRequest $request
     * @throws AuthException
     * @throws FirebaseException
     */
    public function updateDefaultSupportedIdpConfigs(string $name, DefaultSupportedIdpConfigRequest $request) : ResponseInterface
    {
        $uri = sprintf('defaultSupportedIdpConfigs/%s', $name);
        return $this->requestApiWithProjectId($uri, $request->jsonSerialize(), 'PATCH');
    }

    /**
     * @param InboundSamlConfigRequest $request
     * @throws AuthException
     * @throws FirebaseException
     */
    public function createInboundSamlConfigs(InboundSamlConfigRequest $request) : ResponseInterface
    {
        $uri = 'inboundSamlConfigs';
        $query = ['inboundSamlConfigId' => $request->getName()];
        return $this->requestApiWithProjectId($uri, $request->jsonSerialize(), 'POST', $query);
    }

    /**
     * @param string $name
     * @throws AuthException
     * @throws FirebaseException
     */
    public function deleteInboundSamlConfigs(string $name) : ResponseInterface
    {
        $uri = sprintf('inboundSamlConfigs/%s', $name);
        return $this->requestApiWithProjectId($uri, [], 'DELETE');
    }

    /**
     * @param string $name
     * @throws AuthException
     * @throws FirebaseException
     */
    public function getInboundSamlConfigs(string $name) : ResponseInterface
    {
        $uri = sprintf('inboundSamlConfigs/%s', $name);
        return $this->requestApiWithProjectId($uri, [], 'GET');
    }

    /**
     * @param string $name
     * @param InboundSamlConfigRequest $request
     * @throws AuthException
     * @throws FirebaseException
     */
    public function updateInboundSamlConfigs(string $name, InboundSamlConfigRequest $request) : ResponseInterface
    {
        $uri = sprintf('inboundSamlConfigs/%s', $name);
        return $this->requestApiWithProjectId($uri, $request->jsonSerialize(), 'PATCH');
    }

    /**
     * @param OAuthIdpConfigRequest $request
     * @throws AuthException
     * @throws FirebaseException
     */
    public function createOauthIdpConfigs(OAuthIdpConfigRequest $request) : ResponseInterface
    {
        $uri = 'oauthIdpConfigs';

        $query = ['oauthIdpConfigId' => $request->getName()];

        return $this->requestApiWithProjectId($uri, $request->jsonSerialize(), 'POST', $query);
    }

    /**
     * @param  string $name
     * @throws AuthException
     * @throws FirebaseException
     */
    public function deleteOauthIdpConfigs(string $name) : ResponseInterface
    {
        $uri = sprintf('oauthIdpConfigs/%s', $name);
        return $this->requestApiWithProjectId($uri, [], 'DELETE');
    }

    /**
     * @param  string $name
     * @throws AuthException
     * @throws FirebaseException
     */
    public function getOauthIdpConfigs(string $name) : ResponseInterface
    {
        $uri = sprintf('oauthIdpConfigs/%s', $name);
        return $this->requestApiWithProjectId($uri, [], 'GET');
    }

    /**
     * @param  string $name
     * @param OAuthIdpConfigRequest $request
     * @throws AuthException
     * @throws FirebaseException
     */
    public function updateOauthIdpConfigs(string $name, OAuthIdpConfigRequest $request) : ResponseInterface
    {
        $uri = sprintf('oauthIdpConfigs/%s', $name);
        return $this->requestApiWithProjectId($uri, $request->jsonSerialize(), 'PATCH');
    }

    /**
     * @internal
     * @param array<String, bool|string> $data
     * @param array<String, String> $query
     * @throws AuthException
     * @throws FirebaseException
     */
    private function requestApiWithProjectId(string $uri, array $data, string $method = 'POST', array $query = []): ResponseInterface
    {
        if (!$this->projectId) {
            throw new IdentityPlatformError('ProjectId must be specified for this api call');
        }
        $uri = $this->injectTenantId($uri);
        $uri = sprintf('projects/%s/%s', $this->projectId->value(), $uri);

        return $this->requestApi($method, $uri, $data, $query);
    }
    /**
     * @internal
     *
     * @param string $uri
     * @return string
     */
    private function injectTenantId(string $uri) : string
    {
        if ($this->tenantId) {
            $uri = sprintf('tenants/%s/%s', $this->tenantId->toString(), $uri);
        }

        return $uri;
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array<String, bool|string> $data
     * @param array<String, String> $query
     *
     * @throws AuthException
     * @throws FirebaseException
     */
    private function requestApi(string $method, string $uri, array $data, array $query = []): ResponseInterface
    {
        $options = [];

        if (!empty($data)) {
            $options['json'] = $data;
        }
        if (!empty($query)) {
            $options['query'] = $query;
        }

        try {
            return $this->client->request($method, $uri, $options);
        } catch (Throwable $e) {
            throw $this->errorHandler->convertException($e);
        }
    }
}

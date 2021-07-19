<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use Kreait\Firebase\IdentityPlatform\ApiClient;
use Kreait\Firebase\IdentityPlatform\DefaultSupportedIdpConfig;
use Kreait\Firebase\IdentityPlatform\InboundSamlConfig;
use Kreait\Firebase\IdentityPlatform\OAuthIdpConfig;
use Kreait\Firebase\Request\DefaultSupportedIdpConfig as DefaultSupportedIdpConfigRequest;
use Kreait\Firebase\Request\InboundSamlConfig as InboundSamlConfigRequest;
use Kreait\Firebase\Request\OAuthIdpConfig as OAuthIdpConfigRequest;
use Kreait\Firebase\Util\JSON;
use Psr\Http\Message\ResponseInterface;

class IdentityPlatform implements Contract\IdentityPlatform
{
    private ApiClient $client;

    /**
     * @param ApiClient $client
     *
     * @internal
     */
    public function __construct(ApiClient $client)
    {
        $this->client = $client;
    }

    public function listAllDefaultSupportedIdpConfigs(): array
    {
        $response = $this->client->listAllDefaultSupportedIdpConfigs();

        return $this->flatten($this->getResponseAsArray($response));
    }

    public function listDefaultSupportedIdpConfigs(): array
    {
        $response = $this->client->listDefaultSupportedIdpConfigs();
        /**
         * @var array<string>
         */
        $array = $this->getResponseAsArray($response)['defaultSupportedIdpConfigs'] ?? [];

        return \array_map(fn (array $defaultIdpConfig) => DefaultSupportedIdpConfig::withProperties($defaultIdpConfig), $array);
    }

    public function createDefaultSupportedIdpConfigs($properties): DefaultSupportedIdpConfig
    {
        $request = $properties instanceof DefaultSupportedIdpConfigRequest ? $properties : DefaultSupportedIdpConfigRequest::withProperties($properties);

        $response = $this->client->createDefaultSupportedIdpConfigs($request);

        return DefaultSupportedIdpConfig::withProperties($this->getResponseAsArray($response));
    }

    public function deleteDefaultSupportedIdpConfigs(string $name): ResponseInterface
    {
        return $this->client->deleteDefaultSupportedIdpConfigs($name);
    }

    public function getDefaultSupportedIdpConfigs(string $name): DefaultSupportedIdpConfig
    {
        $response = $this->client->getDefaultSupportedIdpConfigs($name);

        return DefaultSupportedIdpConfig::withProperties($this->getResponseAsArray($response));
    }

    public function updateDefaultSupportedIdpConfigs(string $name, $properties): DefaultSupportedIdpConfig
    {
        $request = $properties instanceof DefaultSupportedIdpConfigRequest ? $properties : DefaultSupportedIdpConfigRequest::withProperties($properties);
        $response = $this->client->updateDefaultSupportedIdpConfigs($name, $request);

        return DefaultSupportedIdpConfig::withProperties($this->getResponseAsArray($response));
    }

    public function createInboundSamlConfigs($properties): InboundSamlConfig
    {
        $request = $properties instanceof InboundSamlConfigRequest ? $properties : InboundSamlConfigRequest::withProperties($properties);
        $response = $this->client->createInboundSamlConfigs($request);

        return InboundSamlConfig::withProperties($this->getResponseAsArray($response));
    }

    public function deleteInboundSamlConfigs(string $name): ResponseInterface
    {
        return $this->client->deleteInboundSamlConfigs($name);
    }

    public function getInboundSamlConfigs(string $name): InboundSamlConfig
    {
        $response = $this->client->getInboundSamlConfigs($name);

        return InboundSamlConfig::withProperties($this->getResponseAsArray($response));
    }

    public function updateInboundSamlConfigs(string $name, $properties): InboundSamlConfig
    {
        $request = $properties instanceof InboundSamlConfigRequest ? $properties : InboundSamlConfigRequest::withProperties($properties);
        $response = $this->client->updateInboundSamlConfigs($name, $request);

        return InboundSamlConfig::withProperties($this->getResponseAsArray($response));
    }

    public function createOauthIdpConfigs($properties): OauthIdpConfig
    {
        $request = $properties instanceof OAuthIdpConfigRequest ? $properties : OAuthIdpConfigRequest::withProperties($properties);
        $response = $this->client->createOauthIdpConfigs($request);

        return OAuthIdpConfig::withProperties($this->getResponseAsArray($response));
    }

    public function deleteOauthIdpConfigs(string $name): ResponseInterface
    {
        return $this->client->deleteOauthIdpConfigs($name);
    }

    public function getOauthIdpConfigs(string $name): OauthIdpConfig
    {
        $response = $this->client->getOauthIdpConfigs($name);

        return OAuthIdpConfig::withProperties($this->getResponseAsArray($response));
    }

    public function updateOauthIdpConfigs(string $name, $properties): OauthIdpConfig
    {
        $request = $properties instanceof OAuthIdpConfigRequest ? $properties : OAuthIdpConfigRequest::withProperties($properties);
        $response = $this->client->updateOauthIdpConfigs($name, $request);

        return OAuthIdpConfig::withProperties($this->getResponseAsArray($response));
    }

    /**
     * @internal
     *
     * @return string[]
     */
    private function getResponseAsArray(ResponseInterface $response): array
    {
        return JSON::decode((string) $response->getBody(), true);
    }

    /**
     * Flattens Array.
     *
     * @param array<mixed,mixed> $arr
     * @param array<mixed,mixed> $out
     *
     * @return array<mixed,mixed>
     */
    private function flatten(array $arr, array $out = []): array
    {
        foreach ($arr as $item) {
            if (\is_array($item)) {
                $out = \array_merge($out, $this->flatten($item));
            } else {
                $out[] = $item;
            }
        }

        return $out;
    }
}

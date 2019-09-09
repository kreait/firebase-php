<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use GuzzleHttp\ClientInterface;
use Kreait\Firebase\DynamicLink\CreateDynamicLink;
use Kreait\Firebase\DynamicLink\CreateDynamicLink\FailedToCreateDynamicLink;
use Kreait\Firebase\DynamicLink\ShortenLongDynamicLink;
use Kreait\Firebase\DynamicLink\ShortenLongDynamicLink\FailedToShortenLongDynamicLink;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Value\Url;
use Psr\Http\Message\UriInterface;

final class DynamicLinks
{
    /** @var ClientInterface */
    private $apiClient;

    /** @var Url|null */
    private $defaultDynamicLinksDomain;

    private function __construct(ClientInterface $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public static function withApiClient(ClientInterface $apiClient): self
    {
        return new self($apiClient);
    }

    /**
     * @param mixed $dynamicLinksDomain
     */
    public static function withApiClientAndDefaultDomain(ClientInterface $apiClient, $dynamicLinksDomain): self
    {
        $domainUrl = Url::fromValue($dynamicLinksDomain);

        $service = self::withApiClient($apiClient);
        $service->defaultDynamicLinksDomain = $domainUrl;

        return $service;
    }

    /**
     * @param string|Url|UriInterface|CreateDynamicLink|array|mixed $url
     *
     * @throws InvalidArgumentException
     * @throws FailedToCreateDynamicLink
     */
    public function createUnguessableLink($url): DynamicLink
    {
        return $this->createDynamicLink($url, CreateDynamicLink::WITH_UNGUESSABLE_SUFFIX);
    }

    /**
     * @param string|Url|UriInterface|CreateDynamicLink|array|mixed $url
     *
     * @throws InvalidArgumentException
     * @throws FailedToCreateDynamicLink
     */
    public function createShortLink($url): DynamicLink
    {
        return $this->createDynamicLink($url, CreateDynamicLink::WITH_SHORT_SUFFIX);
    }

    /**
     * @param string|Url|UriInterface|CreateDynamicLink|array|mixed $actionOrParametersOrUrl
     *
     * @throws InvalidArgumentException
     * @throws FailedToCreateDynamicLink
     */
    public function createDynamicLink($actionOrParametersOrUrl, string $suffixType = null): DynamicLink
    {
        $action = $this->ensureCreateAction($actionOrParametersOrUrl);

        /* @noinspection NotOptimalIfConditionsInspection */
        if (!$action->hasDynamicLinkDomain() && $this->defaultDynamicLinksDomain) {
            $action = $action->withDynamicLinkDomain($this->defaultDynamicLinksDomain);
        }

        if ($suffixType && $suffixType === CreateDynamicLink::WITH_SHORT_SUFFIX) {
            $action = $action->withShortSuffix();
        } elseif ($suffixType && $suffixType === CreateDynamicLink::WITH_UNGUESSABLE_SUFFIX) {
            $action = $action->withUnguessableSuffix();
        }

        return (new CreateDynamicLink\GuzzleApiClientHandler($this->apiClient))->handle($action);
    }

    /**
     * @param string|Url|UriInterface|ShortenLongDynamicLink|array|mixed $longDynamicLinkOrAction
     *
     * @throws InvalidArgumentException
     * @throws FailedToShortenLongDynamicLink
     */
    public function shortenLongDynamicLink($longDynamicLinkOrAction, string $suffixType = null): DynamicLink
    {
        $action = $this->ensureShortenAction($longDynamicLinkOrAction);

        if ($suffixType && $suffixType === ShortenLongDynamicLink::WITH_SHORT_SUFFIX) {
            $action = $action->withShortSuffix();
        } elseif ($suffixType && $suffixType === ShortenLongDynamicLink::WITH_UNGUESSABLE_SUFFIX) {
            $action = $action->withUnguessableSuffix();
        }

        return (new ShortenLongDynamicLink\GuzzleApiClientHandler($this->apiClient))->handle($action);
    }

    private function ensureCreateAction($actionOrParametersOrUrl): CreateDynamicLink
    {
        if ($this->isStringable($actionOrParametersOrUrl)) {
            return CreateDynamicLink::forUrl((string) $actionOrParametersOrUrl);
        }

        if (\is_array($actionOrParametersOrUrl)) {
            return CreateDynamicLink::fromArray($actionOrParametersOrUrl);
        }

        if ($actionOrParametersOrUrl instanceof CreateDynamicLink) {
            return $actionOrParametersOrUrl;
        }

        throw new InvalidArgumentException('Unsupported action');
    }

    private function ensureShortenAction($actionOrParametersOrUrl): ShortenLongDynamicLink
    {
        if ($this->isStringable($actionOrParametersOrUrl)) {
            return ShortenLongDynamicLink::forLongDynamicLink((string) $actionOrParametersOrUrl);
        }

        if (\is_array($actionOrParametersOrUrl)) {
            return ShortenLongDynamicLink::fromArray($actionOrParametersOrUrl);
        }

        if ($actionOrParametersOrUrl instanceof ShortenLongDynamicLink) {
            return $actionOrParametersOrUrl;
        }

        throw new InvalidArgumentException('Unsupported action');
    }

    private function isStringable($value): bool
    {
        return \is_string($value) || (\is_object($value) && \method_exists($value, '__toString'));
    }
}

<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use GuzzleHttp\ClientInterface;
use Kreait\Firebase\DynamicLink\CreateDynamicLink;
use Kreait\Firebase\DynamicLink\CreateDynamicLink\GuzzleApiClientHandler;
use Kreait\Firebase\DynamicLink\DynamicLinkStatistics;
use Kreait\Firebase\DynamicLink\GetStatisticsForDynamicLink;
use Kreait\Firebase\DynamicLink\ShortenLongDynamicLink;
use Kreait\Firebase\Value\Url;
use Stringable;

use function is_array;

/**
 * @internal
 */
final class DynamicLinks implements Contract\DynamicLinks
{
    private ClientInterface $apiClient;
    private ?string $defaultDynamicLinksDomain = null;

    private function __construct(ClientInterface $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public static function withApiClient(ClientInterface $apiClient): self
    {
        return new self($apiClient);
    }

    /**
     * @param Stringable|string $dynamicLinksDomain
     */
    public static function withApiClientAndDefaultDomain(ClientInterface $apiClient, $dynamicLinksDomain): self
    {
        $domainUrl = (string) Url::fromValue((string) $dynamicLinksDomain);

        $service = self::withApiClient($apiClient);
        $service->defaultDynamicLinksDomain = $domainUrl;

        return $service;
    }

    public function createUnguessableLink($url): DynamicLink
    {
        return $this->createDynamicLink($url, CreateDynamicLink::WITH_UNGUESSABLE_SUFFIX);
    }

    public function createShortLink($url): DynamicLink
    {
        return $this->createDynamicLink($url, CreateDynamicLink::WITH_SHORT_SUFFIX);
    }

    public function createDynamicLink($actionOrParametersOrUrl, ?string $suffixType = null): DynamicLink
    {
        $action = $this->ensureCreateAction($actionOrParametersOrUrl);

        if ($this->defaultDynamicLinksDomain && !$action->hasDynamicLinkDomain()) {
            $action = $action->withDynamicLinkDomain($this->defaultDynamicLinksDomain);
        }

        if ($suffixType && $suffixType === CreateDynamicLink::WITH_SHORT_SUFFIX) {
            $action = $action->withShortSuffix();
        } elseif ($suffixType && $suffixType === CreateDynamicLink::WITH_UNGUESSABLE_SUFFIX) {
            $action = $action->withUnguessableSuffix();
        }

        return (new GuzzleApiClientHandler($this->apiClient))->handle($action);
    }

    public function shortenLongDynamicLink($longDynamicLinkOrAction, ?string $suffixType = null): DynamicLink
    {
        $action = $this->ensureShortenAction($longDynamicLinkOrAction);

        if ($suffixType && $suffixType === ShortenLongDynamicLink::WITH_SHORT_SUFFIX) {
            $action = $action->withShortSuffix();
        } elseif ($suffixType && $suffixType === ShortenLongDynamicLink::WITH_UNGUESSABLE_SUFFIX) {
            $action = $action->withUnguessableSuffix();
        }

        return (new ShortenLongDynamicLink\GuzzleApiClientHandler($this->apiClient))->handle($action);
    }

    public function getStatistics($dynamicLinkOrAction, ?int $durationInDays = null): DynamicLinkStatistics
    {
        $action = $this->ensureGetStatisticsAction($dynamicLinkOrAction);

        if ($durationInDays) {
            $action = $action->withDurationInDays($durationInDays);
        }

        return (new DynamicLink\GetStatisticsForDynamicLink\GuzzleApiClientHandler($this->apiClient))->handle($action);
    }

    /**
     * @param Stringable|string|CreateDynamicLink|array<string, array<string, string>> $actionOrParametersOrUrl
     */
    private function ensureCreateAction($actionOrParametersOrUrl): CreateDynamicLink
    {
        if (is_array($actionOrParametersOrUrl)) {
            return CreateDynamicLink::fromArray($actionOrParametersOrUrl);
        }

        if ($actionOrParametersOrUrl instanceof CreateDynamicLink) {
            return $actionOrParametersOrUrl;
        }

        return CreateDynamicLink::forUrl((string) $actionOrParametersOrUrl);
    }

    /**
     * @param Stringable|string|ShortenLongDynamicLink|array<string, array<string, string>> $actionOrParametersOrUrl
     */
    private function ensureShortenAction($actionOrParametersOrUrl): ShortenLongDynamicLink
    {
        if (is_array($actionOrParametersOrUrl)) {
            return ShortenLongDynamicLink::fromArray($actionOrParametersOrUrl);
        }

        if ($actionOrParametersOrUrl instanceof ShortenLongDynamicLink) {
            return $actionOrParametersOrUrl;
        }

        return ShortenLongDynamicLink::forLongDynamicLink((string) $actionOrParametersOrUrl);
    }

    /**
     * @param Stringable|string|GetStatisticsForDynamicLink $actionOrUrl
     */
    private function ensureGetStatisticsAction($actionOrUrl): GetStatisticsForDynamicLink
    {
        if ($actionOrUrl instanceof GetStatisticsForDynamicLink) {
            return $actionOrUrl;
        }

        return GetStatisticsForDynamicLink::forLink((string) $actionOrUrl);
    }
}

<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use Kreait\Firebase\DynamicLink\ApiClient;
use Kreait\Firebase\DynamicLink\CreateDynamicLink;
use Kreait\Firebase\DynamicLink\CreateDynamicLink\FailedToCreateDynamicLink;
use Kreait\Firebase\DynamicLink\DynamicLinkStatistics;
use Kreait\Firebase\DynamicLink\GetStatisticsForDynamicLink;
use Kreait\Firebase\DynamicLink\GetStatisticsForDynamicLink\FailedToGetStatisticsForDynamicLink;
use Kreait\Firebase\DynamicLink\ShortenLongDynamicLink;
use Kreait\Firebase\DynamicLink\ShortenLongDynamicLink\FailedToShortenLongDynamicLink;
use Kreait\Firebase\Value\Url;
use Psr\Http\Client\ClientExceptionInterface;
use Stringable;

use function is_array;

/**
 * @internal
 *
 * @deprecated 7.14.0 Firebase Dynamic Links is deprecated and should not be used in new projects. The service will
 *                    shut down on August 25, 2025. The component will remain in the SDK until then, but as the
 *                    Firebase service is deprecated, this component is also deprecated
 *
 * @see https://firebase.google.com/support/dynamic-links-faq Dynamic Links Deprecation FAQ
 *
 * @phpstan-import-type CreateDynamicLinkShape from CreateDynamicLink
 * @phpstan-import-type ShortenLongDynamicLinkShape from ShortenLongDynamicLink
 */
final class DynamicLinks implements Contract\DynamicLinks
{
    /**
     * @param non-empty-string|null $defaultDynamicLinksDomain
     */
    private function __construct(
        private readonly ?string $defaultDynamicLinksDomain,
        private readonly ApiClient $apiClient,
    ) {
    }

    public static function withApiClient(ApiClient $apiClient): self
    {
        return new self(null, $apiClient);
    }

    /**
     * @param Stringable|non-empty-string $dynamicLinksDomain
     */
    public static function withApiClientAndDefaultDomain(ApiClient $apiClient, Stringable|string $dynamicLinksDomain): self
    {
        $domainUrl = Url::fromString($dynamicLinksDomain)->value;

        return new self($domainUrl, $apiClient);
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

        $request = $this->apiClient->createDynamicLinkRequest($action);

        try {
            $response = $this->apiClient->send($request, ['http_errors' => false]);
        } catch (ClientExceptionInterface $e) {
            throw new FailedToCreateDynamicLink('Failed to create dynamic link: '.$e->getMessage(), $e->getCode(), $e);
        }

        if ($response->getStatusCode() === 200) {
            return DynamicLink::fromApiResponse($response);
        }

        throw FailedToCreateDynamicLink::withActionAndResponse($action, $response);
    }

    public function shortenLongDynamicLink($longDynamicLinkOrAction, ?string $suffixType = null): DynamicLink
    {
        $action = $this->ensureShortenAction($longDynamicLinkOrAction);

        if ($suffixType && $suffixType === ShortenLongDynamicLink::WITH_SHORT_SUFFIX) {
            $action = $action->withShortSuffix();
        } elseif ($suffixType && $suffixType === ShortenLongDynamicLink::WITH_UNGUESSABLE_SUFFIX) {
            $action = $action->withUnguessableSuffix();
        }

        $request = $this->apiClient->createShortenLinkRequest($action);

        try {
            $response = $this->apiClient->send($request, ['http_errors' => false]);
        } catch (ClientExceptionInterface $e) {
            throw new FailedToShortenLongDynamicLink('Failed to shorten long dynamic link: '.$e->getMessage(), $e->getCode(), $e);
        }

        if ($response->getStatusCode() === 200) {
            return DynamicLink::fromApiResponse($response);
        }

        throw FailedToShortenLongDynamicLink::withActionAndResponse($action, $response);
    }

    /**
     * @param Stringable|non-empty-string|GetStatisticsForDynamicLink $dynamicLinkOrAction
     * @param positive-int|null $durationInDays
     */
    public function getStatistics(Stringable|string|GetStatisticsForDynamicLink $dynamicLinkOrAction, ?int $durationInDays = null): DynamicLinkStatistics
    {
        $action = $this->ensureGetStatisticsAction($dynamicLinkOrAction);

        if ($durationInDays) {
            $action = $action->withDurationInDays($durationInDays);
        }

        $request = $this->apiClient->createStatisticsRequest($action);

        try {
            $response = $this->apiClient->send($request, ['http_errors' => false]);
        } catch (ClientExceptionInterface $e) {
            throw new FailedToGetStatisticsForDynamicLink('Failed to get statistics for Dynamic Link: '.$e->getMessage(), $e->getCode(), $e);
        }

        if ($response->getStatusCode() === 200) {
            return DynamicLinkStatistics::fromApiResponse($response);
        }

        throw FailedToGetStatisticsForDynamicLink::withActionAndResponse($action, $response);
    }

    /**
     * @param Stringable|non-empty-string|CreateDynamicLink|CreateDynamicLinkShape $actionOrParametersOrUrl
     */
    private function ensureCreateAction(Stringable|string|CreateDynamicLink|array $actionOrParametersOrUrl): CreateDynamicLink
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
     * @param Stringable|non-empty-string|ShortenLongDynamicLink|ShortenLongDynamicLinkShape $actionOrParametersOrUrl
     */
    private function ensureShortenAction(Stringable|string|ShortenLongDynamicLink|array $actionOrParametersOrUrl): ShortenLongDynamicLink
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
     * @param Stringable|non-empty-string|GetStatisticsForDynamicLink $actionOrUrl
     */
    private function ensureGetStatisticsAction(Stringable|string|GetStatisticsForDynamicLink $actionOrUrl): GetStatisticsForDynamicLink
    {
        if ($actionOrUrl instanceof GetStatisticsForDynamicLink) {
            return $actionOrUrl;
        }

        return GetStatisticsForDynamicLink::forLink((string) $actionOrUrl);
    }
}

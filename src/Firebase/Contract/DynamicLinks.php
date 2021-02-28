<?php

declare(strict_types=1);

namespace Kreait\Firebase\Contract;

use InvalidArgumentException;
use Kreait\Firebase\DynamicLink;
use Kreait\Firebase\DynamicLink\CreateDynamicLink;
use Kreait\Firebase\DynamicLink\CreateDynamicLink\FailedToCreateDynamicLink;
use Kreait\Firebase\DynamicLink\DynamicLinkStatistics;
use Kreait\Firebase\DynamicLink\GetStatisticsForDynamicLink;
use Kreait\Firebase\DynamicLink\ShortenLongDynamicLink;
use Kreait\Firebase\DynamicLink\ShortenLongDynamicLink\FailedToShortenLongDynamicLink;
use Kreait\Firebase\Value\Url;
use Psr\Http\Message\UriInterface;

interface DynamicLinks
{
    /**
     * @param string|Url|UriInterface|CreateDynamicLink|array|mixed $url
     *
     * @throws InvalidArgumentException
     * @throws FailedToCreateDynamicLink
     */
    public function createUnguessableLink($url): DynamicLink;

    /**
     * @param string|Url|UriInterface|CreateDynamicLink|array|mixed $url
     *
     * @throws InvalidArgumentException
     * @throws FailedToCreateDynamicLink
     */
    public function createShortLink($url): DynamicLink;

    /**
     * @param string|Url|UriInterface|CreateDynamicLink|array|mixed $actionOrParametersOrUrl
     *
     * @throws InvalidArgumentException
     * @throws FailedToCreateDynamicLink
     */
    public function createDynamicLink($actionOrParametersOrUrl, ?string $suffixType = null): DynamicLink;

    /**
     * @param string|Url|UriInterface|ShortenLongDynamicLink|array|mixed $longDynamicLinkOrAction
     *
     * @throws InvalidArgumentException
     * @throws FailedToShortenLongDynamicLink
     */
    public function shortenLongDynamicLink($longDynamicLinkOrAction, ?string $suffixType = null): DynamicLink;

    /**
     * @param string|Url|UriInterface|GetStatisticsForDynamicLink|mixed $dynamicLinkOrAction
     *
     * @throws InvalidArgumentException
     * @throws GetStatisticsForDynamicLink\FailedToGetStatisticsForDynamicLink
     */
    public function getStatistics($dynamicLinkOrAction, ?int $durationInDays = null): DynamicLinkStatistics;
}

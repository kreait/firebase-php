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

interface DynamicLinks
{
    /**
     * @param \Stringable|string|CreateDynamicLink|array<string, array<string, string>> $url
     *
     * @throws InvalidArgumentException
     * @throws FailedToCreateDynamicLink
     */
    public function createUnguessableLink($url): DynamicLink;

    /**
     * @param \Stringable|string|CreateDynamicLink|array<string, array<string, string>> $url
     *
     * @throws InvalidArgumentException
     * @throws FailedToCreateDynamicLink
     */
    public function createShortLink($url): DynamicLink;

    /**
     * @param \Stringable|string|CreateDynamicLink|array<string, array<string, string>> $actionOrParametersOrUrl
     *
     * @throws InvalidArgumentException
     * @throws FailedToCreateDynamicLink
     */
    public function createDynamicLink($actionOrParametersOrUrl, ?string $suffixType = null): DynamicLink;

    /**
     * @param \Stringable|string|ShortenLongDynamicLink|array<string, array<string, string>> $longDynamicLinkOrAction
     *
     * @throws InvalidArgumentException
     * @throws FailedToShortenLongDynamicLink
     */
    public function shortenLongDynamicLink($longDynamicLinkOrAction, ?string $suffixType = null): DynamicLink;

    /**
     * @param \Stringable|string|GetStatisticsForDynamicLink $dynamicLinkOrAction
     *
     * @throws InvalidArgumentException
     * @throws GetStatisticsForDynamicLink\FailedToGetStatisticsForDynamicLink
     */
    public function getStatistics($dynamicLinkOrAction, ?int $durationInDays = null): DynamicLinkStatistics;
}

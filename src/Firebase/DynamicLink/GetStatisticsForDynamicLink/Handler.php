<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink\GetStatisticsForDynamicLink;

use Kreait\Firebase\DynamicLink\DynamicLinkStatistics;
use Kreait\Firebase\DynamicLink\GetStatisticsForDynamicLink;

/**
 * @internal
 */
interface Handler
{
    public function handle(GetStatisticsForDynamicLink $action): DynamicLinkStatistics;
}

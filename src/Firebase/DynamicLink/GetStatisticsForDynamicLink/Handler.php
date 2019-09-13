<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink\GetStatisticsForDynamicLink;

use Kreait\Firebase\DynamicLink\DynamicLinkStatistics;
use Kreait\Firebase\DynamicLink\GetStatisticsForDynamicLink;

interface Handler
{
    public function handle(GetStatisticsForDynamicLink $action): DynamicLinkStatistics;
}

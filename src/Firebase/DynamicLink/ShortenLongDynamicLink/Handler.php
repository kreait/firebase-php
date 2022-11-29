<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink\ShortenLongDynamicLink;

use Kreait\Firebase\DynamicLink;
use Kreait\Firebase\DynamicLink\ShortenLongDynamicLink;

/**
 * @internal
 */
interface Handler
{
    /**
     * @throws FailedToShortenLongDynamicLink
     */
    public function handle(ShortenLongDynamicLink $action): DynamicLink;
}

<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink\CreateDynamicLink;

use Kreait\Firebase\DynamicLink;
use Kreait\Firebase\DynamicLink\CreateDynamicLink;

interface Handler
{
    /**
     * @throws FailedToCreateDynamicLink
     */
    public function handle(CreateDynamicLink $action): DynamicLink;
}

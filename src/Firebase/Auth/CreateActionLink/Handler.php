<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth\CreateActionLink;

use Kreait\Firebase\Auth\CreateActionLink;

interface Handler
{
    /**
     * @throws FailedToCreateActionLink
     */
    public function handle(CreateActionLink $action): string;
}

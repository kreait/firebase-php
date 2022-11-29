<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth\SendActionLink;

use Kreait\Firebase\Auth\SendActionLink;

/**
 * @internal
 */
interface Handler
{
    /**
     * @throws FailedToSendActionLink
     */
    public function handle(SendActionLink $action): void;
}

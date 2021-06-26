<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth\CreateSessionCookie;

use Kreait\Firebase\Auth\CreateSessionCookie;

interface Handler
{
    /**
     * @throws FailedToCreateSessionCookie
     */
    public function handle(CreateSessionCookie $action): string;
}

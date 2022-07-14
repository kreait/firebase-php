<?php

declare(strict_types=1);

namespace Kreait\Firebase\Http;

/**
 * @internal
 */
interface HasSubRequests
{
    public function subRequests(): Requests;
}

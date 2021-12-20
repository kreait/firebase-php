<?php

declare(strict_types=1);

namespace Kreait\Firebase\Http;

/**
 * @internal
 */
interface HasSubResponses
{
    public function subResponses(): Responses;
}

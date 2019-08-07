<?php

declare(strict_types=1);

namespace Kreait\Firebase\Http;

interface HasSubRequests
{
    public function subRequests(): Requests;
}

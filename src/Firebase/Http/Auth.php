<?php

declare(strict_types=1);

namespace Kreait\Firebase\Http;

use Psr\Http\Message\RequestInterface;

/**
 * @see https://firebase.google.com/docs/auth/server/
 */
interface Auth
{
    /**
     * Returns an authenticated request from the given request.
     */
    public function authenticateRequest(RequestInterface $request): RequestInterface;
}

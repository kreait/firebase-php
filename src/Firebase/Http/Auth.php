<?php

namespace Firebase\Http;

use Psr\Http\Message\RequestInterface;

/**
 * @see https://firebase.google.com/docs/auth/server/
 */
interface Auth
{
    /**
     * Returns an authenticated request from the given request.
     *
     * @param RequestInterface $request
     *
     * @return RequestInterface
     */
    public function authenticateRequest(RequestInterface $request): RequestInterface;
}

<?php

namespace Firebase\Http;

use Psr\Http\Message\RequestInterface;

class Middleware
{
    /**
     * Ensures that the ".json" suffix is added to URIs and that the content type is set correctly
     *
     * @return \Closure
     */
    public static function ensureJson()
    {
        return function (callable $handler) {
            return function (RequestInterface $request, array $options = []) use ($handler) {
                $uri = $request->getUri();
                $path = $uri->getPath();

                if (substr($path, -5) !== '.json') {
                    $uri = $uri->withPath($path.'.json');
                    $request = $request->withUri($uri);
                }

                $request = $request->withHeader('Content-Type', 'application/json');

                return $handler($request, $options);
            };
        };
    }

    /**
     * Adds custom authentication to a request.
     *
     * @param \Firebase\Http\Auth $override
     *
     * @return \Closure
     */
    public static function overrideAuth(Auth $override)
    {
        return function (callable $handler) use ($override) {
            return function (RequestInterface $request, array $options = []) use ($handler, $override) {
                return $handler($override->authenticateRequest($request), $options);
            };
        };
    }
}

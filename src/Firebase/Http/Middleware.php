<?php

namespace Kreait\Firebase\Http;

use Psr\Http\Message\RequestInterface;

class Middleware
{
    /**
     * Ensures that the ".json" suffix is added to URIs and that the content type is set correctly.
     *
     * @return callable
     */
    public static function ensureJsonSuffix(): callable
    {
        return function (callable $handler) {
            return function (RequestInterface $request, array $options = null) use ($handler) {
                $uri = $request->getUri();
                $path = $uri->getPath();

                if (\substr($path, -5) !== '.json') {
                    $uri = $uri->withPath($path.'.json');
                    $request = $request->withUri($uri);
                }

                return $handler($request, $options ?? []);
            };
        };
    }

    /**
     * Adds custom authentication to a request.
     *
     * @param \Kreait\Firebase\Http\Auth $override
     *
     * @return callable
     */
    public static function overrideAuth(Auth $override): callable
    {
        return function (callable $handler) use ($override) {
            return function (RequestInterface $request, array $options = null) use ($handler, $override) {
                return $handler($override->authenticateRequest($request), $options ?? []);
            };
        };
    }
}

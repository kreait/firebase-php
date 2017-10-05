<?php

namespace Kreait\Firebase\Http;

use GuzzleHttp\Psr7;
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
            return function (RequestInterface $request, array $options = []) use ($handler) {
                $uri = $request->getUri();
                $path = $uri->getPath();

                if ('.json' !== substr($path, -5)) {
                    /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                    $uri = $uri->withPath($path.'.json');
                    $request = $request->withUri($uri);
                }

                return $handler($request, $options);
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
            return function (RequestInterface $request, array $options = []) use ($handler, $override) {
                return $handler($override->authenticateRequest($request), $options);
            };
        };
    }

    /**
     * Ensures that the API Key is present as a query parameter.
     *
     * @param string $apiKey
     *
     * @return callable
     */
    public static function ensureApiKey(string $apiKey): callable
    {
        return function (callable $handler) use ($apiKey) {
            return function (RequestInterface $request, array $options = []) use ($handler, $apiKey) {
                $uri = $request->getUri();

                $queryParams = ['key' => $apiKey] + Psr7\parse_query($uri->getQuery());

                /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                $newUri = $uri->withQuery(Psr7\build_query($queryParams));

                /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                $request = $request->withUri($newUri);

                return $handler($request, $options);
            };
        };
    }
}

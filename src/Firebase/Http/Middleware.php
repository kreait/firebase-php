<?php

declare(strict_types=1);

namespace Kreait\Firebase\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Middleware
{
    /**
     * Ensures that the ".json" suffix is added to URIs and that the content type is set correctly.
     */
    public static function ensureJsonSuffix(): callable
    {
        return static function (callable $handler) {
            return static function (RequestInterface $request, array $options = null) use ($handler) {
                $uri = $request->getUri();
                $path = $uri->getPath();

                if (\mb_substr($path, -5) !== '.json') {
                    $uri = $uri->withPath($path.'.json');
                    $request = $request->withUri($uri);
                }

                return $handler($request, $options ?? []);
            };
        };
    }

    /**
     * Adds custom authentication to a request.
     */
    public static function overrideAuth(Auth $override): callable
    {
        return static function (callable $handler) use ($override) {
            return static function (RequestInterface $request, array $options = null) use ($handler, $override) {
                return $handler($override->authenticateRequest($request), $options ?? []);
            };
        };
    }

    /**
     * Parse multipart response body to array of response body.
     */
    public static function parseMultipartResponse(): callable
    {
        return static function (callable $handler) {
            return static function (RequestInterface $request, array $options = null) use ($handler) {
                $promise = $handler($request, $options ?? []);
                return $promise->then(
                    function (ResponseInterface $response) {
                        $contentType = $response->getHeader('Content-Type') ?? [];
                        if (mb_strstr(implode(', ', $contentType), '/', true) === 'multipart') {
                            return new MultipartResponse($response);
                        }
                        return $response;
                    }
                );
            };
        };
    }
}

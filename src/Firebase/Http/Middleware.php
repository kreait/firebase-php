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

                return $handler($request, $options ?: []);
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
                return $handler($override->authenticateRequest($request), $options ?: []);
            };
        };
    }

    /**
     * Parses multi-requests and multi-responses.
     */
    public static function responseWithSubResponses(): callable
    {
        return static function (callable $handler) {
            return static function (RequestInterface $request, array $options = null) use ($handler) {
                return $handler($request, $options ?: [])
                    ->then(static function (ResponseInterface $response) {
                        $isMultiPart = \mb_stristr($response->getHeaderLine('Content-Type'), 'multipart') !== false;
                        $hasMultipleStartLines = ((int) \preg_match_all('@http/[\S]+\s@i', (string) $response->getBody())) > 1;

                        if ($isMultiPart && $hasMultipleStartLines) {
                            return new ResponseWithSubResponses($response);
                        }

                        return $response;
                    });
            };
        };
    }
}

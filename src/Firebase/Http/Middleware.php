<?php

declare(strict_types=1);

namespace Kreait\Firebase\Http;

use GuzzleHttp;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

final class Middleware
{
    /**
     * Ensures that the ".json" suffix is added to URIs and that the content type is set correctly.
     */
    public static function ensureJsonSuffix(): callable
    {
        return static function (callable $handler) {
            return static function (RequestInterface $request, ?array $options = null) use ($handler) {
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
     * Parses multi-requests and multi-responses.
     */
    public static function responseWithSubResponses(): callable
    {
        return static function (callable $handler) {
            return static function (RequestInterface $request, ?array $options = null) use ($handler) {
                return $handler($request, $options ?: [])
                    ->then(static function (ResponseInterface $response) {
                        $isMultiPart = \mb_stristr($response->getHeaderLine('Content-Type'), 'multipart') !== false;
                        $hasMultipleStartLines = ((int) \preg_match_all('@http/[\S]+\s@i', (string) $response->getBody())) >= 1;

                        if ($isMultiPart && $hasMultipleStartLines) {
                            return new ResponseWithSubResponses($response);
                        }

                        return $response;
                    });
            };
        };
    }

    public static function log(LoggerInterface $logger, GuzzleHttp\MessageFormatter $formatter, string $logLevel, string $errorLogLevel): callable
    {
        return static function (callable $handler) use ($logger, $formatter, $logLevel, $errorLogLevel) {
            return static function ($request, array $options) use ($handler, $logger, $formatter, $logLevel, $errorLogLevel) {
                return $handler($request, $options)->then(
                    static function (ResponseInterface $response) use ($logger, $request, $formatter, $logLevel, $errorLogLevel) {
                        $message = $formatter->format($request, $response);
                        $messageLogLevel = $response->getStatusCode() >= 400 ? $errorLogLevel : $logLevel;

                        $logger->log($messageLogLevel, $message);

                        return $response;
                    },
                    static function (\Exception $reason) use ($logger, $request, $formatter, $errorLogLevel) {
                        $response = $reason instanceof GuzzleHttp\Exception\RequestException ? $reason->getResponse() : null;
                        $message = $formatter->format($request, $response, $reason);

                        $logger->log($errorLogLevel, $message, ['request' => $request, 'response' => $response]);

                        return GuzzleHttp\Promise\rejection_for($reason);
                    }
                );
            };
        };
    }
}

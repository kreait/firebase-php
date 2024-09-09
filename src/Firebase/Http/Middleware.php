<?php

declare(strict_types=1);

namespace Kreait\Firebase\Http;

use Beste\Json;
use Closure;
use Exception;
use Fig\Http\Message\StatusCodeInterface as StatusCode;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Query;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

use function array_merge;
use function ltrim;
use function str_ends_with;

/**
 * @internal
 */
final class Middleware
{
    /**
     * Ensures that the ".json" suffix is added to URIs and that the content type is set correctly.
     */
    public static function ensureJsonSuffix(): callable
    {
        return static fn(callable $handler): Closure => static function (RequestInterface $request, ?array $options = null) use ($handler) {
            $uri = $request->getUri();
            $path = '/'.ltrim($uri->getPath(), '/');

            if (!str_ends_with($path, '.json')) {
                $uri = $uri->withPath($path.'.json');
                $request = $request->withUri($uri);
            }

            return $handler($request, $options ?: []);
        };
    }

    /**
     * @param array<string, mixed>|null $override
     */
    public static function addDatabaseAuthVariableOverride(?array $override): callable
    {
        return static fn(callable $handler): Closure => static function (RequestInterface $request, ?array $options = null) use ($handler, $override) {
            $uri = $request->getUri();

            $uri = $uri->withQuery(Query::build(
                array_merge(Query::parse($uri->getQuery()), ['auth_variable_override' => Json::encode($override)]),
            ));

            return $handler($request->withUri($uri), $options ?: []);
        };
    }

    public static function log(LoggerInterface $logger, MessageFormatter $formatter, string $logLevel, string $errorLogLevel): callable
    {
        return static fn(callable $handler): Closure => static fn($request, array $options) => $handler($request, $options)->then(
            static function (ResponseInterface $response) use ($logger, $request, $formatter, $logLevel, $errorLogLevel): ResponseInterface {
                $message = $formatter->format($request, $response);
                $messageLogLevel = $response->getStatusCode() >= StatusCode::STATUS_BAD_REQUEST ? $errorLogLevel : $logLevel;

                $logger->log($messageLogLevel, $message);

                return $response;
            },
            static function (Exception $reason) use ($logger, $request, $formatter, $errorLogLevel): PromiseInterface {
                $response = $reason instanceof RequestException ? $reason->getResponse() : null;
                $message = $formatter->format($request, $response, $reason);

                $logger->log($errorLogLevel, $message, ['request' => $request, 'response' => $response]);

                return Create::rejectionFor($reason);
            },
        );
    }
}

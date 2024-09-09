<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Beste\Json;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * @internal
 */
final class RequestFactory
{
    private readonly bool $environmentSupportsHTTP2;

    public function __construct(
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
    ) {
        $this->environmentSupportsHTTP2 = self::environmentSupportsHTTP2();
    }

    public function createRequest(Message $message, string $projectId, bool $validateOnly): RequestInterface
    {
        $request = $this->requestFactory
            ->createRequest(
                'POST',
                'https://fcm.googleapis.com/v1/projects/'.$projectId.'/messages:send',
            )
        ;

        if ($this->environmentSupportsHTTP2) {
            $request = $request->withProtocolVersion('2.0');
        }

        $payload = ['message' => $message];

        if ($validateOnly === true) {
            $payload['validate_only'] = true;
        }

        $body = $this->streamFactory->createStream(Json::encode($payload));

        return $request
            ->withBody($body)
            ->withHeader('Content-Type', 'application/json; charset=UTF-8')
            ->withHeader('Content-Length', (string) $body->getSize())
        ;
    }

    /**
     * @see https://github.com/microsoftgraph/msgraph-sdk-php/issues/854
     * @see https://github.com/microsoftgraph/msgraph-sdk-php/pull/1120
     *
     * @codeCoverageIgnore
     */
    private static function environmentSupportsHTTP2(): bool
    {
        if (!extension_loaded('curl')) {
            return false;
        }

        if (!defined('CURL_VERSION_HTTP2')) {
            return false;
        }

        $features = curl_version()["features"] ?? null;

        return ($features & CURL_VERSION_HTTP2) === CURL_VERSION_HTTP2;
    }
}

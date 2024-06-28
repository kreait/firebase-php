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
    public function __construct(
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
    ) {
    }

    public function createRequest(Message $message, string $projectId, bool $validateOnly): RequestInterface
    {
        $request = $this->requestFactory
            ->createRequest(
                'POST',
                'https://fcm.googleapis.com/v1/projects/'.$projectId.'/messages:send',
            )
        ;

        $payload = ['message' => $message];

        if ($validateOnly === true) {
            $payload['validate_only'] = true;
        }

        $body = $this->streamFactory->createStream(Json::encode($payload));

        return $request
            ->withProtocolVersion('2.0')
            ->withBody($body)
            ->withHeader('Content-Type', 'application/json; charset=UTF-8')
            ->withHeader('Content-Length', (string) $body->getSize())
        ;
    }
}

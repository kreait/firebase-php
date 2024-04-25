<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Beste\Json;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Pool;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * @internal
 */
class ApiClient
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly string $projectId,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
    ) {
    }

    public function createSendRequestForMessage(Message $message, bool $validateOnly): RequestInterface
    {
        $request = $this->requestFactory
            ->createRequest(
                'POST',
                'https://fcm.googleapis.com/v1/projects/'.$this->projectId.'/messages:send',
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

    /**
     * @param list<RequestInterface>|Iterator<RequestInterface> $requests
     * @param array<string, mixed> $config
     */
    public function pool(array|Iterator $requests, array $config): PromiseInterface
    {
        $pool = new Pool($this->client, $requests, $config);

        return $pool->promise();
    }
}

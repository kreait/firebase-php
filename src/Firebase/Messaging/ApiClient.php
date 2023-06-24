<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Beste\Json;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\PromiseInterface;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingApiExceptionConverter;
use Kreait\Firebase\Exception\MessagingException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Throwable;

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
        private readonly MessagingApiExceptionConverter $errorHandler,
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
            ->withBody($body)
            ->withHeader('Content-Type', 'application/json; charset=UTF-8')
            ->withHeader('Content-Length', (string) $body->getSize())
        ;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @throws FirebaseException
     * @throws MessagingException
     */
    public function send(RequestInterface $request, array $options = []): ResponseInterface
    {
        try {
            return $this->client->send($request, $options);
        } catch (Throwable $e) {
            throw $this->errorHandler->convertException($e);
        }
    }

    /**
     * @param array<string, mixed> $options
     */
    public function sendAsync(RequestInterface $request, array $options = []): PromiseInterface
    {
        return $this->client->sendAsync($request, $options)
            ->then(null, function (Throwable $e): never {
                throw $this->errorHandler->convertException($e);
            })
        ;
    }
}

<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingApiExceptionConverter;
use Kreait\Firebase\Exception\MessagingException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Throwable;

/**
 * @internal
 */
class ApiClient
{
    /** @var ClientInterface */
    private $client;

    /** @var MessagingApiExceptionConverter */
    private $errorHandler;

    /**
     * @internal
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
        $this->errorHandler = new MessagingApiExceptionConverter();
    }

    /**
     * @throws MessagingException
     * @throws FirebaseException
     */
    public function sendMessage(Message $message): ResponseInterface
    {
        return $this->sendMessageAsync($message)->wait();
    }

    public function sendMessageAsync(Message $message): PromiseInterface
    {
        $request = $this->createRequest('POST', 'messages:send');

        return $this->sendAsync($request, [
            'json' => ['message' => $message->jsonSerialize()],
        ]);
    }

    /**
     * @throws MessagingException
     * @throws FirebaseException
     */
    public function validateMessage(Message $message): ResponseInterface
    {
        return $this->validateMessageAsync($message)->wait();
    }

    public function validateMessageAsync(Message $message): PromiseInterface
    {
        $request = $this->createRequest('POST', 'messages:send');

        return $this->sendAsync($request, [
            'json' => [
                'message' => $message->jsonSerialize(),
                'validate_only' => true,
            ],
        ]);
    }

    private function sendAsync(RequestInterface $request, array $options = null): PromiseInterface
    {
        $options = $options ?? [];

        return $this->client->sendAsync($request, $options)
            ->then(null, function (Throwable $e) {
                throw $this->errorHandler->convertException($e);
            });
    }

    private function createRequest(string $method, string $endpoint): Request
    {
        /** @var UriInterface $uri */
        $uri = $this->client->getConfig('base_uri');
        $path = \rtrim($uri->getPath(), '/').'/'.\ltrim($endpoint, '/');
        $uri = $uri->withPath($path);

        return new Request($method, $uri);
    }
}

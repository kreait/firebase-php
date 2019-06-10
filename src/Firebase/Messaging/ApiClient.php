<?php

namespace Kreait\Firebase\Messaging;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use Kreait\Firebase\Exception\MessagingException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Throwable;

class ApiClient
{
    /**
     * @var ClientInterface
     */
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

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
                throw $this->convertError($e);
            });
    }

    private function createRequest(string $method, string $endpoint): Request
    {
        /** @var UriInterface $uri */
        $uri = $this->client->getConfig('base_uri');
        $path = rtrim($uri->getPath(), '/').'/'.ltrim($endpoint, '/');
        $uri = $uri->withPath($path);

        return new Request($method, $uri);
    }

    private function convertError(Throwable $error): MessagingException
    {
        if ($error instanceof RequestException) {
            return MessagingException::fromRequestException($error);
        }

        return new MessagingException($error->getMessage(), $error->getCode(), $error);
    }
}

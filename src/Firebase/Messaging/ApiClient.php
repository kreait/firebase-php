<?php

namespace Kreait\Firebase\Messaging;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Kreait\Firebase\Exception\MessagingException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

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
        return $this->request('POST', 'messages:send', [
            'json' => ['message' => $message->jsonSerialize()],
        ]);
    }

    public function validateMessage(Message $message): ResponseInterface
    {
        return $this->request('POST', 'messages:send', [
            'json' => [
                'message' => $message->jsonSerialize(),
                'validate_only' => true,
            ],
        ]);
    }

    private function request($method, $endpoint, array $options = null): ResponseInterface
    {
        $options = $options ?? [];

        $request = $this->createRequest($method, $endpoint);

        try {
            return $this->client->send($request, $options);
        } catch (RequestException $e) {
            throw MessagingException::fromRequestException($e);
        } catch (\Throwable $e) {
            throw new MessagingException($e->getMessage(), $e->getCode(), $e);
        }
    }

    private function createRequest($method, $endpoint): Request
    {
        /** @var UriInterface $uri */
        $uri = $this->client->getConfig('base_uri');
        $path = rtrim($uri->getPath(), '/').'/'.ltrim($endpoint, '/');
        $uri = $uri->withPath($path);

        return new Request($method, $uri);
    }
}

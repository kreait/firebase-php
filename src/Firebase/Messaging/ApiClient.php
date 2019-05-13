<?php

namespace Kreait\Firebase\Messaging;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
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

    public function sendMessages(array $messages): ResponseInterface
    {
        return $this->request('POST', 'messages:sendAll', [
            'json' => ['messages' => array_map(function($message) {return $message->jsonSerialize();}, $messages)],
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

        /** @var UriInterface $uri */
        $uri = $this->client->getConfig('base_uri');
        $path = rtrim($uri->getPath(), '/').'/'.ltrim($endpoint, '/');
        $uri = $uri->withPath($path);

        try {
            return $this->client->request($method, $uri, $options);
        } catch (RequestException $e) {
            throw MessagingException::fromRequestException($e);
        } catch (\Throwable $e) {
            throw new MessagingException($e->getMessage(), $e->getCode(), $e);
        }
    }
}

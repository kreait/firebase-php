<?php

namespace Kreait\Firebase\Messaging;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Kreait\Firebase\Util\JSON;
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
        // echo JSON::prettyPrint($message); exit;
        return $this->request('POST', 'messages:send', [
            'json' => $message,
        ]);
    }

    private function request($method, $endpoint, array $options = [])
    {
        /** @var UriInterface $uri */
        $uri = $this->client->getConfig('base_uri');
        $path = rtrim($uri->getPath(), '/').'/'.ltrim($endpoint, '/');
        $uri = $uri->withPath($path);

        return $this->client->request($method, $uri, $options);
    }
}

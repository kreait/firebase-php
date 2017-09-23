<?php

namespace Kreait\Firebase\Database;

use GuzzleHttp\Client;
use Kreait\Firebase\Exception\ApiException;

/**
 * SSE Client to retrieve data from streaming rest API
 */
class SseClient
{
    const RETRY_DEFAULT_MS = 3000;
    const END_OF_MESSAGE = "/\r\n\r\n|\n\n|\r\r/";

    /**
     * @var Client
     */
    private $apiClient;

    /**
     *
     * @var Psr\Http\Message\ResponseInterface
     */
    private $response;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string - last received message id
     */
    private $lastId;

    /**
     * @var int - reconnection time in milliseconds
     */
    private $retry = self::RETRY_DEFAULT_MS;

    /**
     * @param ApiClient $apiClient
     * @param string    $url
     */
    public function __construct(ApiClient $apiClient, string $url)
    {
        $this->apiClient = $apiClient;
        $this->url = $url;
    }

    /**
     * Returns generator that yields new event when it's available on stream.
     *
     * @return \Generator|ServerSentEvent[]
     */
    public function getEvents()
    {
        $buffer = '';

        if (!$this->response) {
            $this->connect();
        }

        $body = $this->response->getBody();

        while (true) {
            if ($body->eof()) {
                sleep($this->retry / 1000);
                $this->connect();
                $buffer = '';
            }

            $buffer .= $body->read(1);
            if (preg_match(self::END_OF_MESSAGE, $buffer)) {
                $parts = preg_split(self::END_OF_MESSAGE, $buffer, 2);

                $rawMessage = $parts[0];
                $remaining = $parts[1];

                $buffer = $remaining;
                $event = ServerSentEvent::parse($rawMessage);

                if ($event->getId()) {
                    $this->lastId = $event->getId();
                }

                if ($event->getRetry()) {
                    $this->retry = $event->getRetry();
                }

                yield $event;
            }
        }
    }

    /**
     * Connect to server
     */
    private function connect()
    {
        $headers = [
            'Accept' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
        ];

        if ($this->lastId) {
            $headers['Last-Event-ID'] = $this->lastId;
        }

        $this->response = $this->apiClient->stream($this->url, [
            'stream' => true,
            'headers' => $headers,
        ]);
    }
}

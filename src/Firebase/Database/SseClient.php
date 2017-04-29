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
    private $client;

    /**
     *
     * @var GuzzleHttp\Psr7\Response
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
     * @param Client $client
     * @param string $url
     */
    public function __construct(Client $client, string $url)
    {
        $this->client = $client;
        $this->url = $url;

        $this->connect();
    }

    /**
     * Returns generator that yields new event when it's available on stream.
     *
     * @return Event[]
     */
    public function getEvents()
    {
        $buffer = '';
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
        $headers = [];
        if ($this->lastId) {
            $headers['Last-Event-ID'] = $this->lastId;
        }

        try {
            $this->response = $this->client->request('GET', $this->url, [
                'stream' => true,
                'headers' => $headers,
            ]);
        } catch (\Throwable $e) {
            throw ApiException::wrapThrowable($e);
        }
    }
}

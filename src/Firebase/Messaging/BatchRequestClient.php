<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class BatchRequestClient extends BaseClient
{
    const PART_BOUNDARY = '__END_OF_PART__';

    /**
     * @var string
     */
    protected $batchUri;

    /**
     * @var array
     */
    protected $headers;

    public function __construct(ClientInterface $client, string $batchUri, array $headers = [])
    {
        parent::__construct($client);
        $this->batchUri = $batchUri;
        $this->headers = $headers;
    }

    public function sendBatchRequest(SubRequestCollection $requests): ResponseInterface
    {
        return $this->sendBatchRequestAsync($requests)->wait();
    }

    public function sendBatchRequestAsync(SubRequestCollection $requests): PromiseInterface
    {
        $boundary = self::PART_BOUNDARY;
        return $this->sendAsync(new Request('POST', $this->batchUri), [
            'headers' => [
                'Content-Type' => "multipart/mixed; boundary={$boundary}",
            ],
            'body' => $this->getMultipartPayload($requests, $boundary),
        ]);
    }

    protected function getMultipartPayload(SubRequestCollection $requests, $boundary): string
    {
        $buffer = '';
        foreach ($requests as $idx => $request) {
            $buffer .= $this->createPart($request, $boundary , $idx);
        }
        $buffer .= "--{$boundary}--\r\n";

        return $buffer;
    }

    protected function createPart($request, string $boundary, int $idx): string
    {
        $serializedRequest = $this->serializeSubRequest($request);
        $headers = $this->getHeaders(strlen($serializedRequest), $idx);
        $part = "--{$boundary}\r\n";
        foreach ($headers as $key => $value) {
            $part .= "{$key}: {$value}\r\n";
        }
        $part .= "\r\n";
        $part .= "{$serializedRequest}\r\n";

        return $part;
    }

    protected function serializeSubRequest(RequestInterface $request): string
    {
        $request->withHeader('Content-Length', $request->getBody()->getSize());
        $request->withHeader('Content-Type', 'application/json; charset=UTF-8');

        return \GuzzleHttp\Psr7\str($request);
    }

    protected function getHeaders(int $length, int $idx): array
    {
        return [
            'Content-Length' => $length,
            'Content-Type' => 'application/http',
            'content-id' => $idx + 1,
            'content-transfer-encoding' => 'binary',
        ];
    }
}

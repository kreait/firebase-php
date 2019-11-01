<?php

/** @noinspection ReturnTypeCanBeDeclaredInspection */

declare(strict_types=1);

namespace Kreait\Firebase\Http;

use GuzzleHttp\Psr7\AppendStream;
use GuzzleHttp\Psr7\Request;
use function GuzzleHttp\Psr7\stream_for;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * This is basically a Multipart Request, except that in the parts the sub request start lines
 * are injected between the headers and the body.
 *
 * Header: value
 * Header: value
 *
 * method requestTarget protocolVersion
 *
 * body
 */
final class RequestWithSubRequests implements HasSubRequests, RequestInterface
{
    use WrappedPsr7Request;

    /** @var string */
    private $method = 'POST';

    /** @var string */
    private $boundary;

    /** @var array */
    private $headers;

    /** @var AppendStream */
    private $body;

    /** @var Requests */
    private $subRequests;

    /**
     * @param string|UriInterface $uri
     * @param string $version Protocol version
     */
    public function __construct($uri, Requests $subRequests, $version = '1.1')
    {
        $this->boundary = \sha1(\uniqid('', true));

        $headers = [
            'Content-Type' => 'multipart/mixed; boundary='.$this->boundary,
        ];

        $this->body = new AppendStream();

        $this->subRequests = $subRequests;

        foreach ($subRequests as $request) {
            $this->appendPartForSubRequest($request);
        }
        $this->appendStream("--{$this->boundary}--");

        $request = new Request($this->method, $uri, $headers, $this->body, $version);

        $request = $request->withHeader('Content-Length', $request->getBody()->getSize());

        $this->wrappedRequest = $request;
    }

    public function subRequests(): Requests
    {
        return $this->subRequests;
    }

    private function appendPartForSubRequest(RequestInterface $subRequest)
    {
        $this->appendStream("--{$this->boundary}\r\n");
        $this->appendStream($this->subRequestHeadersAsString($subRequest)."\r\n\r\n");
        $this->appendStream("{$subRequest->getMethod()} {$subRequest->getRequestTarget()} HTTP/{$subRequest->getProtocolVersion()}\r\n\r\n");
        $this->appendStream($subRequest->getBody()."\r\n");
    }

    private function appendStream($value)
    {
        // Objects are passed by reference, we want to ensure that they are not changed
        if ($value instanceof StreamInterface) {
            $value = (string) $value;
        }

        $this->body->addStream(stream_for($value));
    }

    private function subRequestHeadersAsString(RequestInterface $request): string
    {
        $headerNames = \array_keys($request->getHeaders());

        $headers = [];

        foreach ($headerNames as $name) {
            if (\mb_strtolower($name) === 'host') {
                continue;
            }
            $headers[] = "{$name}: {$request->getHeaderLine($name)}";
        }

        return \implode("\r\n", $headers);
    }
}

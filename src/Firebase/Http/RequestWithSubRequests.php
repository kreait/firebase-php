<?php

declare(strict_types=1);

namespace Kreait\Firebase\Http;

use GuzzleHttp\Psr7\AppendStream;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * @internal
 *
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

    private string $method = 'POST';

    private string $boundary;

    private AppendStream $body;

    private Requests $subRequests;

    /**
     * @param string|UriInterface $uri
     * @param string $version Protocol version
     */
    public function __construct($uri, Requests $subRequests, string $version = '1.1')
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

        if (($requestBody = $request->getBody()) !== null) {
            $contentLength = $requestBody->getSize();
            if ($contentLength !== null) {
                $request = $request->withHeader('Content-Length', (string) $contentLength);
            }
        }

        $this->wrappedRequest = $request;
    }

    public function subRequests(): Requests
    {
        return $this->subRequests;
    }

    private function appendPartForSubRequest(RequestInterface $subRequest): void
    {
        $this->appendStream("--{$this->boundary}\r\n");
        $this->appendStream($this->subRequestHeadersAsString($subRequest)."\r\n\r\n");
        $this->appendStream("{$subRequest->getMethod()} {$subRequest->getRequestTarget()} HTTP/{$subRequest->getProtocolVersion()}\r\n\r\n");
        $this->appendStream($subRequest->getBody()."\r\n");
    }

    private function appendStream(string $value): void
    {
        $this->body->addStream(Utils::streamFor($value));
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

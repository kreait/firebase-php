<?php

declare(strict_types=1);

namespace Kreait\Firebase\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * @codeCoverageIgnore
 */
trait WrappedPsr7Request
{
    /** @var RequestInterface */
    protected $wrappedRequest;

    public function getProtocolVersion()
    {
        return $this->wrappedRequest->getProtocolVersion();
    }

    public function withProtocolVersion($version)
    {
        $request = clone $this;
        $request->wrappedRequest = $this->wrappedRequest->withProtocolVersion($version);

        return $request;
    }

    public function getHeaders()
    {
        return $this->wrappedRequest->getHeaders();
    }

    public function hasHeader($name)
    {
        return $this->wrappedRequest->hasHeader($name);
    }

    public function getHeader($name)
    {
        return $this->wrappedRequest->getHeader($name);
    }

    public function getHeaderLine($name)
    {
        return $this->wrappedRequest->getHeaderLine($name);
    }

    public function withHeader($name, $value)
    {
        $request = clone $this;
        $request->wrappedRequest = $this->wrappedRequest->withHeader($name, $value);

        return $request;
    }

    public function withAddedHeader($name, $value)
    {
        $request = clone $this;
        $request->wrappedRequest = $this->wrappedRequest->withAddedHeader($name, $value);

        return $request;
    }

    public function withoutHeader($name)
    {
        $request = clone $this;
        $request->wrappedRequest = $this->wrappedRequest->withoutHeader($name);

        return $request;
    }

    public function getBody()
    {
        return $this->wrappedRequest->getBody();
    }

    public function withBody(StreamInterface $body)
    {
        $request = clone $this;
        $request->wrappedRequest = $this->wrappedRequest->withBody($body);

        return $request;
    }

    public function getRequestTarget()
    {
        return $this->wrappedRequest->getRequestTarget();
    }

    public function withRequestTarget($requestTarget)
    {
        $request = clone $this;
        $request->wrappedRequest = $this->wrappedRequest->withRequestTarget($requestTarget);

        return $request;
    }

    public function getMethod()
    {
        return $this->wrappedRequest->getMethod();
    }

    public function withMethod($method)
    {
        $request = clone $this;
        $request->wrappedRequest = $this->wrappedRequest->withMethod($method);

        return $request;
    }

    public function getUri()
    {
        return $this->wrappedRequest->getUri();
    }

    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $request = clone $this;
        $request->wrappedRequest->withUri($uri, $preserveHost);

        return $request;
    }

    public function subRequests(): Requests
    {
        return $this->wrappedRequest instanceof HasSubRequests
            ? $this->wrappedRequest->subRequests()
            : new Requests();
    }
}

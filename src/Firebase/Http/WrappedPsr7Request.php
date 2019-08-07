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
        return $this->wrappedRequest->withProtocolVersion($version);
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
        return $this->wrappedRequest->withHeader($name, $value);
    }

    public function withAddedHeader($name, $value)
    {
        return $this->wrappedRequest->withAddedHeader($name, $value);
    }

    public function withoutHeader($name)
    {
        return $this->wrappedRequest->withoutHeader($name);
    }

    public function getBody()
    {
        return $this->wrappedRequest->getBody();
    }

    public function withBody(StreamInterface $body)
    {
        return $this->wrappedRequest->withBody($body);
    }

    public function getRequestTarget()
    {
        return $this->wrappedRequest->getRequestTarget();
    }

    public function withRequestTarget($requestTarget)
    {
        return $this->wrappedRequest->withRequestTarget($requestTarget);
    }

    public function getMethod()
    {
        return $this->wrappedRequest->getMethod();
    }

    public function withMethod($method)
    {
        return $this->wrappedRequest->withMethod($method);
    }

    public function getUri()
    {
        return $this->wrappedRequest->getUri();
    }

    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        return $this->wrappedRequest->withUri($uri, $preserveHost);
    }
}

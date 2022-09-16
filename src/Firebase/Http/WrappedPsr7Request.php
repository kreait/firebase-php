<?php

declare(strict_types=1);

namespace Kreait\Firebase\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
trait WrappedPsr7Request
{
    protected RequestInterface $wrappedRequest;

    public function getProtocolVersion(): string
    {
        return $this->wrappedRequest->getProtocolVersion();
    }

    public function withProtocolVersion($version): self
    {
        $request = clone $this;
        $request->wrappedRequest = $this->wrappedRequest->withProtocolVersion($version);

        return $request;
    }

    public function getHeaders(): array
    {
        return $this->wrappedRequest->getHeaders();
    }

    public function hasHeader($name): bool
    {
        return $this->wrappedRequest->hasHeader($name);
    }

    public function getHeader($name): array
    {
        return $this->wrappedRequest->getHeader($name);
    }

    public function getHeaderLine($name): string
    {
        return $this->wrappedRequest->getHeaderLine($name);
    }

    public function withHeader($name, $value): self
    {
        $request = clone $this;
        $request->wrappedRequest = $this->wrappedRequest->withHeader($name, $value);

        return $request;
    }

    public function withAddedHeader($name, $value): self
    {
        $request = clone $this;
        $request->wrappedRequest = $this->wrappedRequest->withAddedHeader($name, $value);

        return $request;
    }

    public function withoutHeader($name): self
    {
        $request = clone $this;
        $request->wrappedRequest = $this->wrappedRequest->withoutHeader($name);

        return $request;
    }

    public function getBody(): StreamInterface
    {
        return $this->wrappedRequest->getBody();
    }

    public function withBody(StreamInterface $body): self
    {
        $request = clone $this;
        $request->wrappedRequest = $this->wrappedRequest->withBody($body);

        return $request;
    }

    public function getRequestTarget(): string
    {
        return $this->wrappedRequest->getRequestTarget();
    }

    public function withRequestTarget($requestTarget): self
    {
        $request = clone $this;
        $request->wrappedRequest = $this->wrappedRequest->withRequestTarget($requestTarget);

        return $request;
    }

    public function getMethod(): string
    {
        return $this->wrappedRequest->getMethod();
    }

    public function withMethod($method): self
    {
        $request = clone $this;
        $request->wrappedRequest = $this->wrappedRequest->withMethod($method);

        return $request;
    }

    public function getUri(): UriInterface
    {
        return $this->wrappedRequest->getUri();
    }

    public function withUri(UriInterface $uri, $preserveHost = false): self
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

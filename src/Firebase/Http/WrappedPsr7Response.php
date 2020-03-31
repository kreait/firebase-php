<?php

declare(strict_types=1);

namespace Kreait\Firebase\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @codeCoverageIgnore
 */
trait WrappedPsr7Response
{
    /** @var ResponseInterface */
    protected $wrappedResponse;

    public function getProtocolVersion(): string
    {
        return $this->wrappedResponse->getProtocolVersion();
    }

    public function withProtocolVersion($version): self
    {
        $response = clone $this;
        $response->wrappedResponse = $this->wrappedResponse->withProtocolVersion($version);

        return $response;
    }

    public function getHeaders(): array
    {
        return $this->wrappedResponse->getHeaders();
    }

    public function hasHeader($name): bool
    {
        return $this->wrappedResponse->hasHeader($name);
    }

    public function getHeader($name): array
    {
        return $this->wrappedResponse->getHeader($name);
    }

    public function getHeaderLine($name): string
    {
        return $this->wrappedResponse->getHeaderLine($name);
    }

    public function withHeader($name, $value): self
    {
        $response = clone $this;
        $response->wrappedResponse = $this->wrappedResponse->withHeader($name, $value);

        return $response;
    }

    public function withAddedHeader($name, $value): self
    {
        $response = clone $this;
        $response->wrappedResponse = $this->wrappedResponse->withAddedHeader($name, $value);

        return $response;
    }

    public function withoutHeader($name): self
    {
        $response = clone $this;
        $response->wrappedResponse = $this->wrappedResponse->withoutHeader($name);

        return $response;
    }

    public function getBody(): StreamInterface
    {
        return $this->wrappedResponse->getBody();
    }

    public function withBody(StreamInterface $body): self
    {
        $response = clone $this;
        $response->wrappedResponse = $this->wrappedResponse->withBody($body);

        return $response;
    }

    public function getStatusCode(): int
    {
        return $this->wrappedResponse->getStatusCode();
    }

    public function withStatus($code, $reasonPhrase = ''): self
    {
        $response = clone $this;
        $response->wrappedResponse = $this->wrappedResponse->withStatus($code, $reasonPhrase);

        return $response;
    }

    public function getReasonPhrase(): string
    {
        return $this->wrappedResponse->getReasonPhrase();
    }
}

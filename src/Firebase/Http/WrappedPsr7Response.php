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

    public function getProtocolVersion()
    {
        return $this->wrappedResponse->getProtocolVersion();
    }

    public function withProtocolVersion($version)
    {
        $response = clone $this;
        $response->wrappedResponse = $this->wrappedResponse->withProtocolVersion($version);

        return $response;
    }

    public function getHeaders()
    {
        return $this->wrappedResponse->getHeaders();
    }

    public function hasHeader($name)
    {
        return $this->wrappedResponse->hasHeader($name);
    }

    public function getHeader($name)
    {
        return $this->wrappedResponse->getHeader($name);
    }

    public function getHeaderLine($name)
    {
        return $this->wrappedResponse->getHeaderLine($name);
    }

    public function withHeader($name, $value): void
    {
        $response = clone $this;
        $response->wrappedResponse = $this->wrappedResponse->withHeader($name, $value);

        return $response;
    }

    public function withAddedHeader($name, $value)
    {
        $response = clone $this;
        $response->wrappedResponse = $this->wrappedResponse->withAddedHeader($name, $value);

        return $response;
    }

    public function withoutHeader($name)
    {
        $response = clone $this;
        $response->wrappedResponse = $this->wrappedResponse->withoutHeader($name);

        return $response;
    }

    public function getBody()
    {
        return $this->wrappedResponse->getBody();
    }

    public function withBody(StreamInterface $body)
    {
        $response = clone $this;
        $response->wrappedResponse = $this->wrappedResponse->withBody($body);

        return $response;
    }

    public function getStatusCode()
    {
        return $this->wrappedResponse->getStatusCode();
    }

    public function withStatus($code, $reasonPhrase = '')
    {
        $response = clone $this;
        $response->wrappedResponse = $this->wrappedResponse->withStatus($code, $reasonPhrase);

        return $response;
    }

    public function getReasonPhrase()
    {
        return $this->wrappedResponse->getReasonPhrase();
    }
}

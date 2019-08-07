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
        return $this->wrappedResponse->withProtocolVersion($version);
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

    public function withHeader($name, $value)
    {
        return $this->wrappedResponse->withHeader($name, $value);
    }

    public function withAddedHeader($name, $value)
    {
        return $this->wrappedResponse->withAddedHeader($name, $value);
    }

    public function withoutHeader($name)
    {
        return $this->wrappedResponse->withoutHeader($name);
    }

    public function getBody()
    {
        return $this->wrappedResponse->getBody();
    }

    public function withBody(StreamInterface $body)
    {
        return $this->wrappedResponse->withBody($body);
    }

    public function getStatusCode()
    {
        return $this->wrappedResponse->getStatusCode();
    }

    public function withStatus($code, $reasonPhrase = '')
    {
        return $this->wrappedResponse->withStatus($code, $reasonPhrase);
    }

    public function getReasonPhrase()
    {
        return $this->wrappedResponse->getReasonPhrase();
    }
}

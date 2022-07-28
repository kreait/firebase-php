<?php

declare(strict_types=1);

namespace Kreait\Firebase\Http;

use Kreait\Firebase\Exception\InvalidArgumentException;

final class HttpClientOptions
{
    /**
     * The amount of seconds to wait while connecting to a server.
     */
    private ?float $connectTimeout = 15;

    /**
     * The amount of seconds to wait while reading a streamed body.
     *
     * Defaults to the value of the default_socket_timeout PHP ini setting.
     */
    private ?float $readTimeout = null;

    /**
     * The amount of seconds to wait for a full request (connect + transfer + read) to complete.
     */
    private ?float $timeout = 30;

    /**
     * The proxy that all requests should be passed through.
     */
    private ?string $proxy = null;

    private function __construct()
    {
    }

    public static function default(): self
    {
        return new self();
    }

    /**
     * The amount of seconds to wait while connecting to a server.
     *
     * Defaults to indefinitely.
     */
    public function connectTimeout(): ?float
    {
        return $this->connectTimeout;
    }

    /**
     * @param float $value the amount of seconds to wait while connecting to a server
     */
    public function withConnectTimeout(float $value): self
    {
        if ($value < 0) {
            throw new InvalidArgumentException('The connect timeout cannot be smaller than zero.');
        }

        $options = clone $this;
        $options->connectTimeout = $value;

        return $options;
    }

    /**
     * The amount of seconds to wait while reading a streamed body.
     *
     * Defaults to the value of the default_socket_timeout PHP ini setting.
     */
    public function readTimeout(): ?float
    {
        return $this->readTimeout;
    }

    /**
     * @param float $value the amount of seconds to wait while reading a streamed body
     */
    public function withReadTimeout(float $value): self
    {
        if ($value < 0) {
            throw new InvalidArgumentException('The read timeout cannot be smaller than zero.');
        }

        $options = clone $this;
        $options->readTimeout = $value;

        return $options;
    }

    /**
     * The amount of seconds to wait for a full request (connect + transfer + read) to complete.
     *
     * Defaults to indefinitely.
     */
    public function timeout(): ?float
    {
        return $this->timeout;
    }

    /**
     * @param float $value the amount of seconds to wait while reading a streamed body
     */
    public function withTimeout(float $value): self
    {
        if ($value < 0) {
            throw new InvalidArgumentException('The total timeout cannot be smaller than zero.');
        }

        $options = clone $this;
        $options->timeout = $value;

        return $options;
    }

    /**
     * The proxy that all requests should be passed through.
     */
    public function proxy(): ?string
    {
        return $this->proxy;
    }

    /**
     * @param string $value the proxy that all requests should be passed through
     */
    public function withProxy(string $value): self
    {
        $options = clone $this;
        $options->proxy = $value;

        return $options;
    }
}

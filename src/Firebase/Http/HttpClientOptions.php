<?php

declare(strict_types=1);

namespace Kreait\Firebase\Http;

use GuzzleHttp\RequestOptions;
use Kreait\Firebase\Exception\InvalidArgumentException;

use function is_callable;

final class HttpClientOptions
{
    /**
     * @param array<non-empty-string, mixed> $guzzleConfig
     * @param list<array{middleware: callable, name: string}> $guzzleMiddlewares
     */
    private function __construct(
        private readonly array $guzzleConfig,
        private readonly array $guzzleMiddlewares,
    ) {
    }

    public static function default(): self
    {
        return new self([], []);
    }

    /**
     * The amount of seconds to wait while connecting to a server.
     *
     * @see RequestOptions::CONNECT_TIMEOUT
     */
    public function connectTimeout(): ?float
    {
        return $this->guzzleConfig[RequestOptions::CONNECT_TIMEOUT] ?? null;
    }

    /**
     * @param float $value the amount of seconds to wait while connecting to a server
     *
     * @see RequestOptions::CONNECT_TIMEOUT
     */
    public function withConnectTimeout(float $value): self
    {
        if ($value < 0) {
            throw new InvalidArgumentException('The connect timeout cannot be smaller than zero.');
        }

        return $this->withGuzzleConfigOption(RequestOptions::CONNECT_TIMEOUT, $value);
    }

    /**
     * The amount of seconds to wait while reading a streamed body.
     *
     * @see RequestOptions::READ_TIMEOUT
     */
    public function readTimeout(): ?float
    {
        return $this->guzzleConfig[RequestOptions::READ_TIMEOUT] ?? null;
    }

    /**
     * @param float $value the amount of seconds to wait while reading a streamed body
     *
     * @see RequestOptions::READ_TIMEOUT
     */
    public function withReadTimeout(float $value): self
    {
        if ($value < 0) {
            throw new InvalidArgumentException('The read timeout cannot be smaller than zero.');
        }

        return $this->withGuzzleConfigOption(RequestOptions::READ_TIMEOUT, $value);
    }

    /**
     * The amount of seconds to wait for a full request (connect + transfer + read) to complete.
     *
     * @see RequestOptions::TIMEOUT
     */
    public function timeout(): ?float
    {
        return $this->guzzleConfig[RequestOptions::TIMEOUT] ?? null;
    }

    /**
     * @param float $value the amount of seconds to wait while reading a streamed body
     *
     * @see RequestOptions::TIMEOUT
     */
    public function withTimeout(float $value): self
    {
        if ($value < 0) {
            throw new InvalidArgumentException('The total timeout cannot be smaller than zero.');
        }

        return $this->withGuzzleConfigOption(RequestOptions::TIMEOUT, $value);
    }

    /**
     * The proxy that all requests should be passed through.
     *
     * @see RequestOptions::PROXY
     */
    public function proxy(): ?string
    {
        return $this->guzzleConfig[RequestOptions::PROXY] ?? null;
    }

    /**
     * @param non-empty-string $value the proxy that all requests should be passed through
     *
     * @see RequestOptions::PROXY
     */
    public function withProxy(string $value): self
    {
        return $this->withGuzzleConfigOption(RequestOptions::PROXY, $value);
    }

    /**
     * @param non-empty-string $name
     */
    public function withGuzzleConfigOption(string $name, mixed $option): self
    {
        $config = $this->guzzleConfig;
        $config[$name] = $option;

        return new self($config, $this->guzzleMiddlewares);
    }

    /**
     * @param array<non-empty-string, mixed> $options
     */
    public function withGuzzleConfigOptions(array $options): self
    {
        $config = $this->guzzleConfig;

        foreach ($options as $name => $option) {
            $config[$name] = $option;
        }

        return new self($config, $this->guzzleMiddlewares);
    }

    /**
     * @return array<non-empty-string, mixed>
     */
    public function guzzleConfig(): array
    {
        return $this->guzzleConfig;
    }

    /**
     * @return list<array{middleware: callable, name: string}>
     */
    public function guzzleMiddlewares(): array
    {
        return $this->guzzleMiddlewares;
    }

    /**
     * @param non-empty-string|null $name
     */
    public function withGuzzleMiddleware(callable $middleware, ?string $name = null): self
    {
        $middlewares = $this->guzzleMiddlewares;
        $middlewares[] = ['middleware' => $middleware, 'name' => $name ?? ''];

        return new self($this->guzzleConfig, $middlewares);
    }

    /**
     * @param list<array{
     *     middleware: callable,
     *     name: string
     * }|callable> $middlewares
     */
    public function withGuzzleMiddlewares(array $middlewares): self
    {
        $newMiddlewares = $this->guzzleMiddlewares;

        foreach ($middlewares as $middleware) {
            if (is_callable($middleware)) {
                $middleware = ['middleware' => $middleware, 'name' => ''];
            }

            $newMiddlewares[] = $middleware;
        }

        return new self($this->guzzleConfig, $newMiddlewares);
    }
}

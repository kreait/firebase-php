<?php

declare(strict_types=1);

namespace Kreait\Firebase\Http;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * @internal
 * @codeCoverageIgnore
 */
trait WrappedGuzzleClient
{
    /** @var ClientInterface */
    protected $client;

    /**
     * @param array<string, mixed> $options
     *
     * @throws GuzzleException
     */
    public function send(RequestInterface $request, array $options = []): ResponseInterface
    {
        return $this->client->send($request, $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function sendAsync(RequestInterface $request, array $options = []): PromiseInterface
    {
        return $this->client->sendAsync($request, $options);
    }

    /**
     * @param string $method
     * @param UriInterface|string $uri
     * @param array<string, mixed> $options
     *
     * @throws GuzzleException
     */
    public function request($method, $uri, array $options = []): ResponseInterface
    {
        return $this->client->request($method, $uri, $options);
    }

    /**
     * @param string $method
     * @param UriInterface|string $uri
     * @param array<string, mixed> $options
     */
    public function requestAsync($method, $uri, array $options = []): PromiseInterface
    {
        return $this->client->requestAsync($method, $uri, $options);
    }

    public function getConfig($option = null)
    {
        return $this->client->getConfig($option);
    }
}

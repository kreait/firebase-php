<?php

namespace Firebase\Database;

use Fig\Http\Message\RequestMethodInterface as RequestMethod;
use Firebase\Exception\ApiException;
use Firebase\Http\Auth;
use Firebase\Http\Middleware;
use Firebase\Util\JSON;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\ResponseInterface;

class ApiClient
{
    /**
     * @var ClientInterface
     */
    protected $httpClient;

    public function __construct(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function withCustomAuth(Auth $auth): ApiClient
    {
        $config = $this->httpClient->getConfig();

        /** @var HandlerStack $stack */
        $stack = clone $config['handler'];
        $stack->push(Middleware::overrideAuth($auth), 'auth_override');

        $config['handler'] = $stack;

        $client = new Client($config);

        return new self($client);
    }

    public function get($uri)
    {
        $response = $this->request(RequestMethod::METHOD_GET, $uri);

        return JSON::decode((string) $response->getBody(), true);
    }

    public function set($uri, $value)
    {
        $response = $this->request(RequestMethod::METHOD_PUT, $uri, ['body' => JSON::encode($value)]);

        return JSON::decode((string) $response->getBody(), true);
    }

    public function push($uri, $value): string
    {
        $response = $this->request(RequestMethod::METHOD_POST, $uri, ['body' => JSON::encode($value)]);

        return JSON::decode((string) $response->getBody(), true)['name'];
    }

    public function remove($uri)
    {
        $this->request(RequestMethod::METHOD_DELETE, $uri);
    }

    public function update($uri, array $values)
    {
        $this->request(RequestMethod::METHOD_PATCH, $uri, ['body' => JSON::encode($values)]);
    }

    private function request(string $method, $uri, array $options = []): ResponseInterface
    {
        try {
            return $this->httpClient->request($method, $uri, $options);
        } catch (\Throwable $e) {
            throw ApiException::wrapThrowable($e);
        }
    }
}

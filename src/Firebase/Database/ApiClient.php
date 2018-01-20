<?php

namespace Kreait\Firebase\Database;

use Fig\Http\Message\RequestMethodInterface as RequestMethod;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use Kreait\Firebase\Exception\ApiException;
use Kreait\Firebase\Http\Auth;
use Kreait\Firebase\Http\Middleware;
use Kreait\Firebase\Util\JSON;
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

    public function withCustomAuth(Auth $auth): self
    {
        $config = $this->httpClient->getConfig();

        /** @var HandlerStack $stack */
        $stack = clone $config['handler'];
        $stack->remove('auth_override');
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
        $response = $this->request(RequestMethod::METHOD_PUT, $uri, ['json' => $value]);

        return JSON::decode((string) $response->getBody(), true);
    }

    public function push($uri, $value): string
    {
        $response = $this->request(RequestMethod::METHOD_POST, $uri, ['json' => $value]);

        return JSON::decode((string) $response->getBody(), true)['name'];
    }

    public function remove($uri)
    {
        $this->request(RequestMethod::METHOD_DELETE, $uri);
    }

    public function update($uri, array $values)
    {
        $this->request(RequestMethod::METHOD_PATCH, $uri, ['json' => $values]);
    }

    private function request(string $method, $uri, array $options = []): ResponseInterface
    {
        $request = new Request($method, $uri);

        try {
            return $this->httpClient->send($request, $options);
        } catch (RequestException $e) {
            throw ApiException::wrapRequestException($e);
        } catch (\Throwable $e) {
            throw new ApiException($request, $e->getMessage(), $e->getCode(), $e);
        }
    }
}

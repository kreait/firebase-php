<?php

namespace Firebase\Database;

use Firebase\Exception\ApiException;
use Firebase\Exception\IndexNotDefined;
use Firebase\Exception\PermissionDenied;
use Firebase\Http\Auth;
use Firebase\Http\Middleware;
use Firebase\Util\JSON;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\HandlerStack;

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
        try {
            $response = $this->httpClient->request('GET', $uri);
        } catch (\Throwable $e) {
            return $this->handleThrowable($e);
        }

        return JSON::decode((string) $response->getBody(), true);
    }

    public function set($uri, $value)
    {
        try {
            $response = $this->httpClient->request('PUT', $uri, ['body' => JSON::encode($value)]);
        } catch (\Throwable $e) {
            return $this->handleThrowable($e);
        }

        return JSON::decode((string) $response->getBody(), true);
    }

    public function push($uri, $value): string
    {
        try {
            $response = $this->httpClient->request('POST', $uri, ['body' => JSON::encode($value)]);
        } catch (\Throwable $e) {
            return $this->handleThrowable($e);
        }

        $responseData = JSON::decode((string) $response->getBody(), true);

        if (!($responseData['name'] ?? null)) {
            throw new ApiException('The API should have returned the name of the new child, but it hasn\'t.');
        }

        return (string) $responseData['name'];
    }

    public function remove($uri)
    {
        try {
            $this->httpClient->request('DELETE', $uri);
        } catch (\Throwable $e) {
            $this->handleThrowable($e);
        }
    }

    public function update($uri, array $values)
    {
        try {
            $this->httpClient->request('PATCH', $uri, ['body' => JSON::encode($values)]);
        } catch (\Throwable $e) {
            $this->handleThrowable($e);
        }
    }

    /**
     * @param \Throwable $e
     *
     * @throws ApiException
     */
    private function handleThrowable(\Throwable $e)
    {
        if ($e instanceof ClientException) {
            $this->handleClientException($e);
        }

        throw new ApiException($e->getMessage(), $e->getCode(), $e);
    }

    /**
     * @param ClientException $e
     *
     * @throws ApiException
     */
    private function handleClientException(ClientException $e)
    {
        $message = $e->getMessage();

        if ($e->hasResponse()) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($apiError = JSON::decode((string) $response->getBody(), true)['error'] ?? null) {
                $message = $apiError;
            }

            if (in_array($statusCode, [401, 403])) {
                throw new PermissionDenied($message, $e->getCode(), $e);
            }

            if ($statusCode === 400 && stripos($message, 'index not defined') !== false) {
                throw new IndexNotDefined($message, $e->getCode(), $e);
            }
        }

        throw new ApiException($message, $e->getCode(), $e);
    }
}

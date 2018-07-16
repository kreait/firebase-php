<?php

namespace Kreait\Firebase\Database;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Kreait\Firebase\Exception\ApiException;
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

    public function get($uri)
    {
        $response = $this->request('GET', $uri);

        return JSON::decode((string) $response->getBody(), true);
    }

    public function set($uri, $value)
    {
        $response = $this->request('PUT', $uri, ['json' => $value]);

        return JSON::decode((string) $response->getBody(), true);
    }

    public function push($uri, $value): string
    {
        $response = $this->request('POST', $uri, ['json' => $value]);

        return JSON::decode((string) $response->getBody(), true)['name'];
    }

    public function remove($uri)
    {
        $this->request('DELETE', $uri);
    }

    public function update($uri, array $values)
    {
        $this->request('PATCH', $uri, ['json' => $values]);
    }

    private function request(string $method, $uri, array $options = []): ResponseInterface
    {
        $request = new Request($method, $uri);

        try {
            // GuzzleException is a marker interface that we cannot catch (at least not in <7.1)
            /** @noinspection PhpUnhandledExceptionInspection */
            return $this->httpClient->send($request, $options);
        } catch (RequestException $e) {
            throw ApiException::wrapRequestException($e);
        } catch (\Throwable $e) {
            throw new ApiException($request, $e->getMessage(), $e->getCode(), $e);
        }
    }
}

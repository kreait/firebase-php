<?php

namespace Kreait\Firebase\Database;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Kreait\Firebase\Exception\ApiException;
use Kreait\Firebase\Util\JSON;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

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

    /**
     * @internal This method should only be used in the context of Database transations
     *
     * @param UriInterface|string $uri
     *
     * @return array
     */
    public function getWithETag($uri): array
    {
        $response = $this->request('GET', $uri, [
            'headers' => [
                'X-Firebase-ETag' => 'true',
            ],
        ]);

        $value = JSON::decode((string) $response->getBody(), true);
        $etag = $response->getHeaderLine('ETag');

        return [
            'value' => $value,
            'etag' => $etag,
        ];
    }

    public function set($uri, $value)
    {
        $response = $this->request('PUT', $uri, ['json' => $value]);

        return JSON::decode((string) $response->getBody(), true);
    }

    /**
     * @internal This method should only be used in the context of Database transations
     *
     * @param UriInterface|string $uri
     * @param mixed $value
     * @param string $etag
     *
     * @return mixed
     */
    public function setWithEtag($uri, $value, string $etag)
    {
        $response = $this->request('PUT', $uri, [
            'headers' => [
                'if-match' => $etag,
            ],
            'json' => $value,
        ]);

        return JSON::decode((string) $response->getBody(), true);
    }

    /**
     * @internal This method should only be used in the context of Database transations
     *
     * @param UriInterface|string $uri
     * @param string $etag
     */
    public function removeWithEtag($uri, string $etag)
    {
        $this->request('DELETE', $uri, [
            'headers' => [
                'if-match' => $etag,
            ],
        ]);
    }

    public function updateRules($uri, RuleSet $ruleSet)
    {
        $response = $this->request('PUT', $uri, [
            'body' => json_encode($ruleSet, JSON_PRETTY_PRINT),
        ]);

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

    private function request(string $method, $uri, array $options = null): ResponseInterface
    {
        $options = $options ?? [];

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

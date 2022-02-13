<?php

declare(strict_types=1);

namespace Kreait\Firebase\Database;

use Beste\Json;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use Kreait\Firebase\Exception\DatabaseApiExceptionConverter;
use Kreait\Firebase\Exception\DatabaseException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Throwable;

/**
 * @internal
 */
class ApiClient
{
    private ClientInterface $client;
    protected DatabaseApiExceptionConverter $errorHandler;

    public function __construct(ClientInterface $httpClient)
    {
        $this->client = $httpClient;
        $this->errorHandler = new DatabaseApiExceptionConverter();
    }

    /**
     * @param UriInterface|string $uri
     *
     * @throws DatabaseException
     *
     * @return mixed
     */
    public function get($uri)
    {
        $response = $this->requestApi('GET', $uri);

        return Json::decode((string) $response->getBody(), true);
    }

    /**
     * @param UriInterface|string $uri
     *
     * @throws DatabaseException
     *
     * @return array<string, mixed>
     */
    public function getWithETag($uri): array
    {
        $response = $this->requestApi('GET', $uri, [
            'headers' => [
                'X-Firebase-ETag' => 'true',
            ],
        ]);

        $value = Json::decode((string) $response->getBody(), true);
        $etag = $response->getHeaderLine('ETag');

        return [
            'value' => $value,
            'etag' => $etag,
        ];
    }

    /**
     * @param UriInterface|string $uri
     * @param mixed $value
     *
     * @throws DatabaseException
     *
     * @return mixed
     */
    public function set($uri, $value)
    {
        $response = $this->requestApi('PUT', $uri, ['json' => $value]);

        return Json::decode((string) $response->getBody(), true);
    }

    /**
     * @param UriInterface|string $uri
     * @param mixed $value
     *
     * @throws DatabaseException
     *
     * @return mixed
     */
    public function setWithEtag($uri, $value, string $etag)
    {
        $response = $this->requestApi('PUT', $uri, [
            'headers' => [
                'if-match' => $etag,
            ],
            'json' => $value,
        ]);

        return Json::decode((string) $response->getBody(), true);
    }

    /**
     * @param UriInterface|string $uri
     *
     * @throws DatabaseException
     */
    public function removeWithEtag($uri, string $etag): void
    {
        $this->requestApi('DELETE', $uri, [
            'headers' => [
                'if-match' => $etag,
            ],
        ]);
    }

    /**
     * @param UriInterface|string $uri
     *
     * @throws DatabaseException
     *
     * @return mixed
     */
    public function updateRules($uri, RuleSet $ruleSet)
    {
        $response = $this->requestApi('PUT', $uri, [
            'body' => Json::encode($ruleSet, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        ]);

        return Json::decode((string) $response->getBody(), true);
    }

    /**
     * @param UriInterface|string $uri
     * @param mixed $value
     *
     * @throws DatabaseException
     */
    public function push($uri, $value): string
    {
        $response = $this->requestApi('POST', $uri, ['json' => $value]);

        return Json::decode((string) $response->getBody(), true)['name'];
    }

    /**
     * @param UriInterface|string $uri
     *
     * @throws DatabaseException
     */
    public function remove($uri): void
    {
        $this->requestApi('DELETE', $uri);
    }

    /**
     * @param UriInterface|string $uri
     * @param array<mixed> $values
     *
     * @throws DatabaseException
     */
    public function update($uri, array $values): void
    {
        $this->requestApi('PATCH', $uri, ['json' => $values]);
    }

    /**
     * @param UriInterface|string $uri
     * @param array<string, mixed>|null $options
     *
     * @throws DatabaseException
     */
    private function requestApi(string $method, $uri, ?array $options = null): ResponseInterface
    {
        $options ??= [];

        $request = new Request($method, $uri);

        try {
            return $this->client->send($request, $options);
        } catch (Throwable $e) {
            throw $this->errorHandler->convertException($e);
        }
    }
}

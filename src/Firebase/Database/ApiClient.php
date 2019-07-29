<?php

declare(strict_types=1);

namespace Kreait\Firebase\Database;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use Kreait\Firebase\Exception\DatabaseApiExceptionConverter;
use Kreait\Firebase\Exception\DatabaseException;
use Kreait\Firebase\Util\JSON;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Throwable;

/**
 * @internal
 */
class ApiClient
{
    /** @var ClientInterface */
    protected $httpClient;

    /** @var DatabaseApiExceptionConverter */
    protected $errorHandler;

    /**
     * @internal
     */
    public function __construct(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
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
        $response = $this->request('GET', $uri);

        return JSON::decode((string) $response->getBody(), true);
    }

    /**
     * @internal This method should only be used in the context of Database transations
     *
     * @param UriInterface|string $uri
     *
     * @throws DatabaseException
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
        $response = $this->request('PUT', $uri, ['json' => $value]);

        return JSON::decode((string) $response->getBody(), true);
    }

    /**
     * @internal This method should only be used in the context of Database transations
     *
     * @param UriInterface|string $uri
     * @param mixed $value
     *
     * @throws DatabaseException
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
     *
     * @throws DatabaseException
     */
    public function removeWithEtag($uri, string $etag)
    {
        $this->request('DELETE', $uri, [
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
        $response = $this->request('PUT', $uri, [
            'body' => \json_encode($ruleSet, \JSON_PRETTY_PRINT),
        ]);

        return JSON::decode((string) $response->getBody(), true);
    }

    /**
     * @param UriInterface|string $uri
     * @param mixed $value
     *
     * @throws DatabaseException
     */
    public function push($uri, $value): string
    {
        $response = $this->request('POST', $uri, ['json' => $value]);

        return JSON::decode((string) $response->getBody(), true)['name'];
    }

    /**
     * @param UriInterface|string $uri
     *
     * @throws DatabaseException
     */
    public function remove($uri)
    {
        $this->request('DELETE', $uri);
    }

    /**
     * @param UriInterface|string $uri
     *
     * @throws DatabaseException
     */
    public function update($uri, array $values)
    {
        $this->request('PATCH', $uri, ['json' => $values]);
    }

    /**
     * @param UriInterface|string $uri
     *
     * @throws DatabaseException
     */
    private function request(string $method, $uri, array $options = null): ResponseInterface
    {
        $options = $options ?? [];

        $request = new Request($method, $uri);

        try {
            return $this->httpClient->send($request, $options);
        } catch (Throwable $e) {
            throw $this->errorHandler->convertException($e);
        }
    }
}

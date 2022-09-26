<?php

declare(strict_types=1);

namespace Kreait\Firebase\Database;

use Beste\Json;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Query;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Exception\DatabaseApiExceptionConverter;
use Kreait\Firebase\Exception\DatabaseException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * @internal
 */
class ApiClient
{
    protected DatabaseApiExceptionConverter $errorHandler;
    private ClientInterface $client;
    private UrlBuilder $resourceUrlBuilder;

    public function __construct(ClientInterface $httpClient, UrlBuilder $resourceUrlBuilder)
    {
        $this->client = $httpClient;
        $this->errorHandler = new DatabaseApiExceptionConverter();
        $this->resourceUrlBuilder = $resourceUrlBuilder;
    }

    /**
     * @throws DatabaseException
     *
     * @return mixed
     */
    public function get(string $path)
    {
        $response = $this->requestApi('GET', $path);

        return Json::decode((string) $response->getBody(), true);
    }

    /**
     * @throws DatabaseException
     *
     * @return array<string, mixed>
     */
    public function getWithETag(string $path): array
    {
        $response = $this->requestApi('GET', $path, [
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
     * @param mixed $value
     *
     * @throws DatabaseException
     *
     * @return mixed
     */
    public function set(string $path, $value)
    {
        $response = $this->requestApi('PUT', $path, ['json' => $value]);

        return Json::decode((string) $response->getBody(), true);
    }

    /**
     * @param mixed $value
     *
     * @throws DatabaseException
     *
     * @return mixed
     */
    public function setWithEtag(string $path, $value, string $etag)
    {
        $response = $this->requestApi('PUT', $path, [
            'headers' => [
                'if-match' => $etag,
            ],
            'json' => $value,
        ]);

        return Json::decode((string) $response->getBody(), true);
    }

    /**
     * @throws DatabaseException
     */
    public function removeWithEtag(string $path, string $etag): void
    {
        $this->requestApi('DELETE', $path, [
            'headers' => [
                'if-match' => $etag,
            ],
        ]);
    }

    /**
     * @throws DatabaseException
     *
     * @return mixed
     */
    public function updateRules(string $path, RuleSet $ruleSet)
    {
        $rules = $ruleSet->getRules();
        $encodedRules = Json::encode((object) $rules);

        $response = $this->requestApi('PUT', $path, [
            'body' => $encodedRules,
        ]);

        return Json::decode((string) $response->getBody(), true);
    }

    /**
     * @param mixed $value
     *
     * @throws DatabaseException
     */
    public function push(string $path, $value): string
    {
        $response = $this->requestApi('POST', $path, ['json' => $value]);

        return Json::decode((string) $response->getBody(), true)['name'];
    }

    /**
     * @throws DatabaseException
     */
    public function remove(string $path): void
    {
        $this->requestApi('DELETE', $path);
    }

    /**
     * @param array<array-key, mixed> $values
     *
     * @throws DatabaseException
     */
    public function update(string $path, array $values): void
    {
        $this->requestApi('PATCH', $path, ['json' => $values]);
    }

    /**
     * @param array<string, mixed>|null $options
     *
     * @throws DatabaseException
     */
    private function requestApi(string $method, string $path, ?array $options = []): ResponseInterface
    {
        $options ??= [];

        $uri = new Uri($path);

        $url = $this->resourceUrlBuilder->getUrl(
            $uri->getPath(),
            Query::parse($uri->getQuery()),
        );

        $request = new Request($method, $url);

        try {
            return $this->client->send($request, $options);
        } catch (Throwable $e) {
            throw $this->errorHandler->convertException($e);
        }
    }
}

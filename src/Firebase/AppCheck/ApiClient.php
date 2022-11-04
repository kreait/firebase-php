<?php

declare(strict_types=1);

namespace Kreait\Firebase\AppCheck;

use Beste\Json;
use GuzzleHttp\ClientInterface;
use Kreait\Firebase\Exception\AppCheckApiExceptionConverter;
use Kreait\Firebase\Exception\AppCheckException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Throwable;

/**
 * @internal
 */
class ApiClient
{
    private ClientInterface $client;
    private AppCheckApiExceptionConverter $errorHandler;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
        $this->errorHandler = new AppCheckApiExceptionConverter();
    }

    /**
     * @param string $appId
     * @param string $customToken
     * 
     * @throws AppCheckException
     * 
     * @return array<string, mixed>
     */
    public function exchangeCustomToken(string $appId, string $customToken): array
    {
        $response = $this->requestApi('POST', 'apps/'.$appId, [
            'headers' => [
                'Content-Type' => 'application/json; UTF-8',
            ],
            'body' => Json::encode([
                'customToken' => $customToken,
            ]),
        ]);

        return Json::decode((string) $response->getBody(), true);
    }

    /**
     * @param string|UriInterface $uri
     * @param array<string, mixed>|null $options
     *
     * @throws AppCheckException
     */
    private function requestApi(string $method, $uri, ?array $options = null): ResponseInterface
    {
        $options ??= [];

        try {
            return $this->client->request($method, $uri, $options);
        } catch (Throwable $e) {
            throw $this->errorHandler->convertException($e);
        }
    }
}

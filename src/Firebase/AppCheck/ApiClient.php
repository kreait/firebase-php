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
 *
 * @phpstan-import-type AppCheckTokenShape from AppCheckToken
 */
final class ApiClient
{
    private readonly AppCheckApiExceptionConverter $errorHandler;

    public function __construct(
        private readonly ClientInterface $client,
    ) {
        $this->errorHandler = new AppCheckApiExceptionConverter();
    }

    /**
     * @throws AppCheckException
     *
     * @return AppCheckTokenShape
     */
    public function exchangeCustomToken(string $appId, string $customToken): array
    {
        $response = $this->requestApi('POST', 'apps/'.$appId.':exchangeCustomToken', [
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
     * @param non-empty-string $method
     * @param string|UriInterface $uri
     * @param array<string, mixed>|null $options
     *
     * @throws AppCheckException
     */
    private function requestApi(string $method, string|UriInterface $uri, ?array $options = null): ResponseInterface
    {
        $options ??= [];

        try {
            return $this->client->request($method, $uri, $options);
        } catch (Throwable $e) {
            throw $this->errorHandler->convertException($e);
        }
    }
}

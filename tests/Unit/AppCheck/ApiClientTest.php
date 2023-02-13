<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\AppCheck;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Kreait\Firebase\AppCheck\ApiClient;
use Kreait\Firebase\Exception\AppCheck\AppCheckError;
use Kreait\Firebase\Exception\AppCheck\PermissionDenied;
use Kreait\Firebase\Exception\AppCheckException;
use Kreait\Firebase\Tests\UnitTestCase;
use Throwable;

/**
 * @internal
 */
final class ApiClientTest extends UnitTestCase
{
    private MockHandler $mockHandler;
    private ApiClient $client;
    private string $appId;
    private string $customToken;

    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler();
        $this->client = new ApiClient(new Client(['handler' => $this->mockHandler]));
        $this->appId = 'com.example.app';
        $this->customToken = 'custom-token';
    }

    /**
     * @dataProvider requestExceptions
     *
     * @param class-string<object> $expectedClass
     */
    public function testCatchRequestException(RequestException $requestException, string $expectedClass): void
    {
        $this->mockHandler->append($requestException);

        try {
            $this->client->exchangeCustomToken($this->appId, $this->customToken);
        } catch (Throwable $e) {
            $this->assertInstanceOf(AppCheckException::class, $e);
            $this->assertInstanceOf($expectedClass, $e);
        }
    }

    public function testCatchThrowable(): void
    {
        $this->mockHandler->append(new Exception());

        $this->expectException(AppCheckException::class);

        $this->client->exchangeCustomToken($this->appId, $this->customToken);
    }

    /**
     * @return array<array<Throwable|class-string>>
     */
    public static function requestExceptions(): array
    {
        $request = new Request('GET', 'https://example.com');

        return [
            [
                new RequestException('Bad Request', $request, new Response(400, [], '{"error":{"message":"BAD_REQUEST"}}')),
                AppCheckError::class,
            ],
            [
                new RequestException('Unauthorized', $request, new Response(401, [], '{"error":{"message":"UNAUTHORIZED"}}')),
                PermissionDenied::class,
            ],
            [
                new RequestException('Forbidden', $request, new Response(403, [], '{"error":{"message":"FORBIDDEN"}}')),
                PermissionDenied::class,
            ],
        ];
    }
}

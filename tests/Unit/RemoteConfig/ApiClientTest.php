<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\RemoteConfig;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Kreait\Firebase\Exception\RemoteConfig\OperationAborted;
use Kreait\Firebase\Exception\RemoteConfig\PermissionDenied;
use Kreait\Firebase\Exception\RemoteConfig\RemoteConfigError;
use Kreait\Firebase\Exception\RemoteConfigException;
use Kreait\Firebase\RemoteConfig\ApiClient;
use Kreait\Firebase\Tests\UnitTestCase;
use Throwable;

/**
 * @internal
 */
final class ApiClientTest extends UnitTestCase
{
    private MockHandler $mockHandler;

    private ApiClient $client;

    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler();
        $this->client = new ApiClient(new Client(['handler' => $this->mockHandler]));
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
            $this->client->getTemplate();
        } catch (Throwable $e) {
            $this->assertInstanceOf(RemoteConfigException::class, $e);
            $this->assertInstanceOf($expectedClass, $e);
        }
    }

    public function testCatchThrowable(): void
    {
        $this->mockHandler->append(new \Exception());

        $this->expectException(RemoteConfigException::class);

        $this->client->getTemplate();
    }

    /**
     * @return array<array<Throwable|class-string>>
     */
    public function requestExceptions(): array
    {
        $request = new Request('GET', 'https://example.com');

        return [
            [
                new RequestException('Bad Request', $request, new Response(400, [], '{"error":{"message":"ABORTED"}}')),
                OperationAborted::class,
            ],
            [
                new RequestException('Bad Request', $request, new Response(400, [], '{"error":{"message":"PERMISSION_DENIED"}}')),
                PermissionDenied::class,
            ],
            [
                new RequestException('Forbidden', $request, new Response(403, [], '{"error":{"message":"UNKOWN"}}')),
                RemoteConfigError::class,
            ],
        ];
    }
}

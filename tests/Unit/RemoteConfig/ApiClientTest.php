<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\RemoteConfig;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Kreait\Firebase\Exception\RemoteConfig\OperationAborted;
use Kreait\Firebase\Exception\RemoteConfig\PermissionDenied;
use Kreait\Firebase\Exception\RemoteConfigException;
use Kreait\Firebase\RemoteConfig\ApiClient;
use Kreait\Firebase\Tests\UnitTestCase;

class ApiClientTest extends UnitTestCase
{
    private $http;

    /** @var ApiClient */
    private $client;

    protected function setUp()
    {
        $this->http = $this->createMock(ClientInterface::class);
        $this->client = new ApiClient($this->http);
    }

    /**
     * @dataProvider requestExceptions
     */
    public function testCatchRequestException($requestException, $expectedClass)
    {
        $this->http->expects($this->once())
            ->method('request')
            ->willThrowException($requestException);

        try {
            $this->client->getTemplate();
        } catch (\Throwable $e) {
            $this->assertInstanceOf(RemoteConfigException::class, $e);
            $this->assertInstanceOf($expectedClass, $e);
        }
    }

    public function testCatchThrowable()
    {
        $this->http->expects($this->once())
            ->method('request')
            ->willThrowException(new \Exception());

        $this->expectException(RemoteConfigException::class);

        $this->client->getTemplate();
    }

    public function requestExceptions(): array
    {
        $request = new Request('GET', 'http://example.com');

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
                RemoteConfigException::class,
            ],
        ];
    }
}

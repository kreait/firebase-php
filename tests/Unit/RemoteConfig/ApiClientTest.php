<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\RemoteConfig;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Kreait\Firebase\Exception\RemoteConfigException;
use Kreait\Firebase\RemoteConfig\ApiClient;
use Kreait\Firebase\Tests\UnitTestCase;
use Psr\Http\Message\RequestInterface;

class ApiClientTest extends UnitTestCase
{
    /**
     * @var ClientInterface
     */
    private $http;

    /**
     * @var ApiClient
     */
    private $client;

    protected function setUp()
    {
        $this->http = $this->createMock(ClientInterface::class);
        $this->client = new ApiClient($this->http);
    }

    public function testCatchRequestException()
    {
        $request = $this->prophesize(RequestInterface::class);

        $this->http
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new RequestException('Foo', $request->reveal()));

        $this->expectException(RemoteConfigException::class);
        $this->client->getTemplate();
    }

    public function testCatchThrowable()
    {
        $this->http
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new \Exception());

        $this->expectException(RemoteConfigException::class);
        $this->client->getTemplate();
    }
}

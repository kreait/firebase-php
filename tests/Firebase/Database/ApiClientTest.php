<?php

namespace Kreait\Tests\Firebase\Database;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Kreait\Firebase\Database\ApiClient;
use Kreait\Firebase\Exception\ApiException;
use Kreait\Firebase\Http\Auth;
use Kreait\Tests\FirebaseTestCase;
use Psr\Http\Message\ResponseInterface;

class ApiClientTest extends FirebaseTestCase
{
    /**
     * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $http;

    /**
     * @var ApiClient
     */
    private $client;

    /**
     * @var string
     */
    private $targetUrl;

    protected function setUp()
    {
        $this->http = $this->createMock(ClientInterface::class);
        $this->client = new ApiClient($this->http);
        $this->targetUrl = 'http://domain.tld/some/path';
    }

    public function testWithCustomAuth()
    {
        $auth = $this->createMock(Auth::class);

        $this->http
            ->expects($this->any())
            ->method('getConfig')
            ->willReturn(['handler' => $this->createMock(HandlerStack::class)]);

        $this->assertInstanceOf(ApiClient::class, $this->client->withCustomAuth($auth));
        $this->assertNotSame($this->client, $this->client->withCustomAuth($auth));
    }

    public function testGet()
    {
        $client = $this->createApiClient();

        $this->assertNotNull($client->get($this->targetUrl));
    }

    public function testSet()
    {
        $client = $this->createApiClient();

        $this->assertNotNull($client->set($this->targetUrl, 'any'));
    }

    public function testPush()
    {
        $client = $this->createApiClient();

        $this->assertNotNull($client->push($this->targetUrl, 'any'));
    }

    public function testUpdate()
    {
        $client = $this->createApiClient();

        $this->assertNull($client->update($this->targetUrl, ['any', 'values'])); // => no return value, no exception
    }

    public function testRemove()
    {
        $client = $this->createApiClient();

        $this->assertNull($client->remove($this->targetUrl)); // => no return value, no exception
    }

    public function testCatchAnyException()
    {
        $this->http->expects($this->any())
            ->method($this->anything())
            ->willThrowException(new \Exception());

        $this->expectException(ApiException::class);

        $this->client->get($this->targetUrl);
    }

    private function createApiClient(ResponseInterface $response = null)
    {
        $client = $this->createMock(ClientInterface::class);

        $response = $response ?? new Response(200, [], '{"name":"value"}');

        $client->expects($this->any())
            ->method($this->anything())
            ->willReturn($response);

        return new ApiClient($client);
    }
}

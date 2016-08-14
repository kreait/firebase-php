<?php

namespace Tests\Firebase\Database;

use Firebase\Database\ApiClient;
use Firebase\Exception\ApiException;
use Firebase\Http\Auth;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Tests\FirebaseTestCase;

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
            ->willReturn([
                'handler' => $this->createMock(HandlerStack::class)
            ]);

        $this->assertInstanceOf(ApiClient::class, $this->client->withCustomAuth($auth));
        $this->assertNotSame($this->client, $this->client->withCustomAuth($auth));
    }

    public function testGet()
    {
        $client = $this->createApiClient();

        $this->assertNotNull($client->get($this->targetUrl));
    }

    public function testWrapGetClientException()
    {
        $this->expectException(ApiException::class);

        $client = $this->createApiClientForClientExceptionTesting();
        $client->get($this->targetUrl);
    }

    public function testSet()
    {
        $client = $this->createApiClient();

        $this->assertNotNull($client->set($this->targetUrl, 'any'));
    }

    public function testWrapSetClientException()
    {
        $this->expectException(ApiException::class);

        $client = $this->createApiClientForClientExceptionTesting();
        $client->set($this->targetUrl, 'any');
    }

    public function testPush()
    {
        $client = $this->createApiClient();

        $this->assertNotNull($client->push($this->targetUrl, 'any'));
    }

    public function testPushWithUnexpectedResponse()
    {
        $client = $this->createApiClient(new Response(200, [], '""'));

        $this->expectException(ApiException::class);

        $client->push($this->targetUrl, 'any');
    }

    public function testWrapPushClientException()
    {
        $this->expectException(ApiException::class);

        $client = $this->createApiClientForClientExceptionTesting();
        $client->push($this->targetUrl, 'any');
    }

    public function testUpdate()
    {
        $client = $this->createApiClient();

        $this->assertNull($client->update($this->targetUrl, ['any', 'values'])); // => no return value, no exception
    }

    public function testWrapUpdateClientException()
    {
        $this->expectException(ApiException::class);

        $client = $this->createApiClientForClientExceptionTesting();
        $client->update($this->targetUrl, ['any', 'values']);
    }

    public function testRemove()
    {
        $client = $this->createApiClient();

        $this->assertNull($client->remove($this->targetUrl)); // => no return value, no exception
    }

    public function testWrapRemoveClientException()
    {
        $this->expectException(ApiException::class);

        $client = $this->createApiClientForClientExceptionTesting();
        $client->remove($this->targetUrl);
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

    /**
     * @return ApiClient
     */
    private function createApiClientForClientExceptionTesting()
    {
        $client = $this->createMock(ClientInterface::class);

        $requestException = RequestException::create(
            new Request('METHOD', $this->targetUrl),
            new Response(400, ['X-Firebase-Auth-Debug' => 'some debug message'], '{"error": "Some error"}')
        );

        $client->expects($this->any())
            ->method($this->anything())
            ->willThrowException($requestException);

        return new ApiClient($client);
    }
}

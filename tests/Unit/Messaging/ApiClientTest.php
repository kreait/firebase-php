<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Exception\Messaging\AuthenticationError;
use Kreait\Firebase\Exception\Messaging\InvalidArgument;
use Kreait\Firebase\Exception\Messaging\ServerError;
use Kreait\Firebase\Exception\Messaging\ServerUnavailable;
use Kreait\Firebase\Exception\Messaging\UnknownError;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Messaging\ApiClient;
use Kreait\Firebase\Messaging\MessageToTopic;
use PHPUnit\Framework\TestCase;

class ApiClientTest extends TestCase
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
        $this->http
            ->expects($this->any())
            ->method('getConfig')
            ->with('base_uri')
            ->willReturn(new Uri('http://example.com'));

        $this->client = new ApiClient($this->http);
    }

    /**
     * @param $requestException
     * @param $expectedClass
     * @dataProvider requestExceptions
     */
    public function testCatchRequestException($requestException, $expectedClass)
    {
        $this->http->expects($this->once())
            ->method('request')
            ->willThrowException($requestException);

        try {
            $this->client->sendMessage(MessageToTopic::create('a-topic'));
        } catch (\Throwable $e) {
            $this->assertInstanceOf(MessagingException::class, $e);
            $this->assertInstanceOf($expectedClass, $e);
        }
    }

    public function testCatchAnyException()
    {
        $this->http->expects($this->once())
            ->method('request')
            ->willThrowException(new \Exception());

        $this->expectException(MessagingException::class);

        $this->client->sendMessage(MessageToTopic::create('a-topic'));
    }

    public function requestExceptions(): array
    {
        $request = new Request('GET', 'http://example.com');
        $responseBody = '{}';

        return [
            [
                new RequestException('Bad Request', $request, new Response(400, [], $responseBody)),
                InvalidArgument::class,
            ],
            [
                new RequestException('Unauthorized', $request, new Response(401, [], $responseBody)),
                AuthenticationError::class,
            ],
            [
                new RequestException('Forbidden', $request, new Response(403, [], $responseBody)),
                AuthenticationError::class,
            ],
            [
                new RequestException('Internal Server Error', $request, new Response(500, [], $responseBody)),
                ServerError::class,
            ],
            [
                new RequestException('Service Unavailable', $request, new Response(503, [], $responseBody)),
                ServerUnavailable::class,
            ],
            [
                new RequestException('I\'m a teapot', $request, new Response(418, [], $responseBody)),
                UnknownError::class,
            ],
        ];
    }
}

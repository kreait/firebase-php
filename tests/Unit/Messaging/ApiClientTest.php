<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Kreait\Firebase\Exception\Messaging\ApiConnectionFailed;
use Kreait\Firebase\Exception\Messaging\AuthenticationError;
use Kreait\Firebase\Exception\Messaging\InvalidMessage;
use Kreait\Firebase\Exception\Messaging\MessagingError;
use Kreait\Firebase\Exception\Messaging\ServerError;
use Kreait\Firebase\Exception\Messaging\ServerUnavailable;
use Kreait\Firebase\Exception\MessagingApiExceptionConverter;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Messaging\ApiClient;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * @internal
 */
final class ApiClientTest extends TestCase
{
    private MockHandler $mock;

    private ApiClient $client;

    protected function setUp(): void
    {
        $this->mock = new MockHandler();
        $handler = HandlerStack::create($this->mock);
        $client = new Client([
            'handler' => $handler,
            'base_uri' => 'http://example.com',
        ]);

        $this->client = new ApiClient($client, new MessagingApiExceptionConverter());
    }

    /**
     * @dataProvider requestExceptions
     *
     * @param mixed $requestException
     * @param mixed $expectedClass
     */
    public function testCatchRequestException($requestException, $expectedClass): void
    {
        $this->mock->append($requestException);

        $this->expectException($expectedClass);
        $this->client->send(new Request('GET', 'http://example.com'));
    }

    public function testCatchAnyException(): void
    {
        $this->mock->append(new Exception());

        $this->expectException(MessagingException::class);

        $this->client->send(new Request('GET', 'https://example.com'));
    }

    /**
     * @return array<array<Throwable|class-string>>
     */
    public function requestExceptions(): array
    {
        $request = new Request('GET', 'https://example.com');
        $responseBody = '{}';

        return [
            [
                new RequestException('Bad Request', $request, new Response(400, [], $responseBody)),
                InvalidMessage::class,
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
                MessagingError::class,
            ],
            [
                new ConnectException('Connection failed', $request),
                ApiConnectionFailed::class,
            ],
        ];
    }
}

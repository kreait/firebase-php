<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Messaging\AppInstanceApiClient;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class AppInstanceApiClientTest extends TestCase
{
    /** @var MockHandler */
    private $mock;

    /** @var AppInstanceApiClient */
    private $client;

    protected function setUp(): void
    {
        $this->mock = new MockHandler();

        $handler = HandlerStack::create($this->mock);
        $client = new Client([
            'handler' => $handler,
            'base_uri' => 'http://example.com',
        ]);

        $this->client = new AppInstanceApiClient($client);
    }

    public function testRequestExceptionIsConvertedToMessagingException(): void
    {
        $this->mock->append(new RequestException('Foo', new Request('POST', 'https://fake.org')));

        $this->expectException(MessagingException::class);
        $this->client->subscribeToTopic('foo', ['bar']);
    }

    public function testAnyThrowableIsConvertedToMessagingException(): void
    {
        $this->mock->append(new \Exception('Foo', 999));

        $this->expectException(MessagingException::class);
        $this->client->subscribeToTopic('foo', ['bar']);
    }
}

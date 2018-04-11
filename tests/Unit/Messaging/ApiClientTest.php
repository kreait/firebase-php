<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Uri;
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

    public function testCatchAnyException()
    {
        $this->http->expects($this->any())
            ->method('request')
            ->willThrowException(new \Exception());

        $this->expectException(MessagingException::class);

        $this->client->sendMessage(MessageToTopic::create('a-topic'));
    }
}

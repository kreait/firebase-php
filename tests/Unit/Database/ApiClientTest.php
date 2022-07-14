<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Kreait\Firebase\Database\ApiClient;
use Kreait\Firebase\Exception\DatabaseException;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
final class ApiClientTest extends UnitTestCase
{
    /** @var ClientInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $http;

    private ApiClient $client;

    private string $targetUrl;

    protected function setUp(): void
    {
        $this->http = $this->createMock(ClientInterface::class);
        $this->client = new ApiClient($this->http);
        $this->targetUrl = 'http://domain.tld/some/path';
    }

    public function testGet(): void
    {
        $client = $this->createApiClient();

        $this->assertNotNull($client->get($this->targetUrl));
    }

    public function testSet(): void
    {
        $client = $this->createApiClient();

        $this->assertNotNull($client->set($this->targetUrl, 'any'));
    }

    public function testPush(): void
    {
        $client = $this->createApiClient();

        $this->assertNotNull($client->push($this->targetUrl, 'any'));
    }

    public function testUpdate(): void
    {
        $this->createApiClient()->update($this->targetUrl, ['any', 'values']);
        $this->addToAssertionCount(1);
    }

    public function testRemove(): void
    {
        $this->createApiClient()->remove($this->targetUrl);
        $this->addToAssertionCount(1);
    }

    public function testCatchRequestException(): void
    {
        $request = new Request('GET', 'foo');

        $this->http
            ->method($this->anything())
            ->willThrowException(new RequestException('foo', $request))
        ;

        $this->expectException(DatabaseException::class);

        $this->client->get($this->targetUrl);
    }

    public function testCatchAnyException(): void
    {
        $this->http
            ->method($this->anything())
            ->willThrowException(new \Exception())
        ;

        $this->expectException(DatabaseException::class);

        $this->client->get($this->targetUrl);
    }

    private function createApiClient(): ApiClient
    {
        $client = $this->createMock(ClientInterface::class);

        $client
            ->method($this->anything())
            ->willReturn(new Response(200, [], '{"name":"value"}'))
        ;

        return new ApiClient($client);
    }
}

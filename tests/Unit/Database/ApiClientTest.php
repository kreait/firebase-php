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
use Psr\Http\Message\ResponseInterface;

/**
 * @internal
 */
class ApiClientTest extends UnitTestCase
{
    /** @var ClientInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $http;

    /** @var ApiClient */
    private $client;

    /** @var string */
    private $targetUrl;

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
        $client = $this->createApiClient();

        $this->assertNull($client->update($this->targetUrl, ['any', 'values'])); // => no return value, no exception
    }

    public function testRemove(): void
    {
        $client = $this->createApiClient();

        $this->assertNull($client->remove($this->targetUrl)); // => no return value, no exception
    }

    public function testCatchRequestException(): void
    {
        $request = new Request('GET', 'foo');

        $this->http
            ->method($this->anything())
            ->willThrowException(new RequestException('foo', $request));

        $this->expectException(DatabaseException::class);

        $this->client->get($this->targetUrl);
    }

    public function testCatchAnyException(): void
    {
        $this->http
            ->method($this->anything())
            ->willThrowException(new \Exception());

        $this->expectException(DatabaseException::class);

        $this->client->get($this->targetUrl);
    }

    private function createApiClient(?ResponseInterface $response = null)
    {
        $client = $this->createMock(ClientInterface::class);

        $response = $response ?? new Response(200, [], '{"name":"value"}');

        $client
            ->method($this->anything())
            ->willReturn($response);

        return new ApiClient($client);
    }
}

<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database;

use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Kreait\Firebase\Database\ApiClient;
use Kreait\Firebase\Database\UrlBuilder;
use Kreait\Firebase\Exception\DatabaseException;
use Kreait\Firebase\Tests\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 */
final class ApiClientTest extends UnitTestCase
{
    /** @var ClientInterface|MockObject */
    private $http;
    private ApiClient $client;
    private string $targetUrl;

    protected function setUp(): void
    {
        $this->targetUrl = 'https://namespace.db.tld/';

        $this->http = $this->createMock(ClientInterface::class);
        $this->http
            ->method($this->anything())
            ->willReturn(new Response(200, [], '{"name":"value"}'));
        $this->client = new ApiClient($this->http, UrlBuilder::create($this->targetUrl));
    }

    public function testGet(): void
    {
        $this->assertNotNull($this->client->get($this->targetUrl));
    }

    public function testSet(): void
    {
        $this->assertNotNull($this->client->set($this->targetUrl, 'any'));
    }

    public function testPush(): void
    {
        $this->assertNotNull($this->client->push($this->targetUrl, 'any'));
    }

    public function testUpdate(): void
    {
        $this->client->update($this->targetUrl, ['any', 'values']);
        $this->addToAssertionCount(1);
    }

    public function testRemove(): void
    {
        $this->client->remove($this->targetUrl);
        $this->addToAssertionCount(1);
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
            ->willThrowException(new Exception());

        $this->expectException(DatabaseException::class);

        $this->client->get($this->targetUrl);
    }
}

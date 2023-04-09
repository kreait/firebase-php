<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database\ApiClient;
use Kreait\Firebase\Database\Query;
use Kreait\Firebase\Database\Reference;
use Kreait\Firebase\Exception\Database\DatabaseError;
use Kreait\Firebase\Exception\Database\DatabaseNotFound;
use Kreait\Firebase\Exception\Database\UnsupportedQuery;
use Kreait\Firebase\Tests\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

/**
 * @internal
 */
final class QueryTest extends UnitTestCase
{
    protected Uri $uri;

    /**
     * @var Reference|MockObject
     */
    protected $reference;

    /**
     * @var ApiClient|MockObject
     */
    protected $apiClient;
    protected Query $query;

    protected function setUp(): void
    {
        $this->uri = new Uri('http://domain.tld/some/path');

        $reference = $this->createMock(Reference::class);
        $reference->method('getURI')->willReturn($this->uri);

        $this->reference = $reference;

        $this->apiClient = $this->createMock(ApiClient::class);

        $this->query = new Query($this->reference, $this->apiClient);
    }

    public function testGetReference(): void
    {
        $this->assertSame($this->reference, $this->query->getReference());
    }

    public function testGetSnapshot(): void
    {
        $this->apiClient->method('get')->with($this->anything())->willReturn('value');

        $this->query->orderByKey()->equalTo(2)->getSnapshot();

        $this->addToAssertionCount(1);
    }

    public function testGetValue(): void
    {
        $this->apiClient->method('get')->with($this->anything())->willReturn('value');

        $this->assertSame('value', $this->query->getValue());
    }

    public function testGetUri(): void
    {
        $uri = $this->query->getUri();

        $this->assertSame((string) $uri, (string) $this->query);
    }

    public function testOnlyOneSorterIsAllowed(): void
    {
        try {
            $this->query->orderByKey()->orderByValue();
        } catch (Throwable $e) {
            $this->assertInstanceOf(UnsupportedQuery::class, $e);
        }
    }

    public function testWrapsApiExceptions(): void
    {
        $exception = new DatabaseError();

        $this->apiClient
            ->method('get')->with($this->anything())
            ->willThrowException($exception)
        ;

        $this->expectException(UnsupportedQuery::class);

        $this->query->getSnapshot();
    }

    public function testIndexNotDefined(): void
    {
        $this->apiClient
            ->method('get')->with($this->anything())
            ->willThrowException(new DatabaseError('foo index not defined bar'))
        ;

        $this->expectException(UnsupportedQuery::class);

        $this->query->getSnapshot();
    }

    public function testWithNonExistingDatabase(): void
    {
        $this->apiClient
            ->method('get')->with($this->anything())
            ->willThrowException(DatabaseNotFound::fromUri(new Uri('https://database-name.firebaseio.com')))
        ;

        $this->expectException(DatabaseNotFound::class);

        $this->query->getSnapshot();
    }
}

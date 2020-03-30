<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database\ApiClient;
use Kreait\Firebase\Database\Query;
use Kreait\Firebase\Database\Reference;
use Kreait\Firebase\Database\Snapshot;
use Kreait\Firebase\Exception\Database\DatabaseError;
use Kreait\Firebase\Exception\Database\UnsupportedQuery;
use Kreait\Firebase\Tests\UnitTestCase;
use Psr\Http\Message\UriInterface;
use Throwable;

/**
 * @internal
 */
class QueryTest extends UnitTestCase
{
    /** @var Uri */
    protected $uri;

    /** @var Reference|\PHPUnit\Framework\MockObject\MockObject */
    protected $reference;

    /** @var ApiClient|\PHPUnit\Framework\MockObject\MockObject */
    protected $apiClient;

    /** @var Query */
    protected $query;

    protected function setUp(): void
    {
        $this->uri = new Uri('http://domain.tld/some/path');

        $reference = $this->createMock(Reference::class);
        $reference->expects($this->any())->method('getURI')->willReturn($this->uri);

        $this->reference = $reference;

        $apiClient = $this->createMock(ApiClient::class);
        $this->apiClient = $apiClient;

        $this->query = new Query($this->reference, $this->apiClient);
    }

    public function testGetReference(): void
    {
        $this->assertSame($this->reference, $this->query->getReference());
    }

    public function testGetSnapshot(): void
    {
        $this->apiClient->expects($this->any())->method('get')->with($this->anything())->willReturn('value');

        $snapshot = $this->query->orderByKey()->equalTo(2)->getSnapshot();

        $this->assertInstanceOf(Snapshot::class, $snapshot);
    }

    public function testGetValue(): void
    {
        $this->apiClient->expects($this->any())->method('get')->with($this->anything())->willReturn('value');

        $this->assertSame('value', $this->query->getValue());
    }

    public function testGetUri(): void
    {
        $uri = $this->query->getUri();

        $this->assertInstanceOf(UriInterface::class, $uri);
        $this->assertSame((string) $uri, (string) $this->query);
    }

    public function testModifiersReturnQueries(): void
    {
        $this->assertInstanceOf(Query::class, $this->query->equalTo('x'));
        $this->assertInstanceOf(Query::class, $this->query->endAt('x'));
        $this->assertInstanceOf(Query::class, $this->query->limitToFirst(1));
        $this->assertInstanceOf(Query::class, $this->query->limitToLast(1));
        $this->assertInstanceOf(Query::class, $this->query->orderByChild('child'));
        $this->assertInstanceOf(Query::class, $this->query->orderByKey());
        $this->assertInstanceOf(Query::class, $this->query->orderByValue());
        $this->assertInstanceOf(Query::class, $this->query->shallow());
        $this->assertInstanceOf(Query::class, $this->query->startAt('x'));
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
            ->willThrowException($exception);

        $this->expectException(UnsupportedQuery::class);

        $this->query->getSnapshot();
    }

    public function testIndexNotDefined(): void
    {
        $this->apiClient
            ->method('get')->with($this->anything())
            ->willThrowException(new DatabaseError('foo index not defined bar'));

        try {
            $this->query->getSnapshot();
            $this->fail('An exception should have been thrown');
        } catch (Throwable $e) {
            $this->assertInstanceOf(UnsupportedQuery::class, $e);
        }
    }
}

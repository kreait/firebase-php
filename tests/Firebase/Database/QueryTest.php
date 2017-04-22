<?php

namespace Kreait\Tests\Firebase\Database;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database\ApiClient;
use Kreait\Firebase\Database\Query;
use Kreait\Firebase\Database\Reference;
use Kreait\Firebase\Database\Snapshot;
use Kreait\Firebase\Exception\ApiException;
use Kreait\Firebase\Exception\IndexNotDefined;
use Kreait\Firebase\Exception\QueryException;
use Kreait\Tests\FirebaseTestCase;
use Psr\Http\Message\UriInterface;

class QueryTest extends FirebaseTestCase
{
    /**
     * @var Uri
     */
    protected $uri;

    /**
     * @var Reference|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $reference;

    /**
     * @var ApiClient|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $apiClient;

    /**
     * @var Query
     */
    protected $query;

    protected function setUp()
    {
        $this->uri = new Uri('http://domain.tld/some/path');

        $reference = $this->createMock(Reference::class);
        $reference->expects($this->any())->method('getURI')->willReturn($this->uri);

        $this->reference = $reference;

        $apiClient = $this->createMock(ApiClient::class);
        $this->apiClient = $apiClient;

        $this->query = new Query($this->reference, $this->apiClient);
    }

    public function testGetReference()
    {
        $this->assertSame($this->reference, $this->query->getReference());
    }

    public function testGetSnapshot()
    {
        $this->apiClient->expects($this->any())->method('get')->with($this->anything())->willReturn('value');

        $snapshot = $this->query->orderByKey()->equalTo(2)->getSnapshot();

        $this->assertInstanceOf(Snapshot::class, $snapshot);
    }

    public function testGetValue()
    {
        $this->apiClient->expects($this->any())->method('get')->with($this->anything())->willReturn('value');

        $this->assertSame('value', $this->query->getValue());
    }

    public function testGetUri()
    {
        $uri = $this->query->getUri();

        $this->assertInstanceOf(UriInterface::class, $uri);
        $this->assertSame((string) $uri, (string) $this->query);
    }

    public function testModifiersReturnQueries()
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

    public function testOnlyOneSorterIsAllowed()
    {
        $this->expectException(QueryException::class);

        $this->query->orderByKey()->orderByValue();
    }

    public function testWrapsApiExceptions()
    {
        $exception = $this->createMock(ApiException::class);

        $this->apiClient
            ->expects($this->any())
            ->method('get')->with($this->anything())
            ->willThrowException($exception);

        $this->expectException(QueryException::class);

        $this->query->getSnapshot();
    }

    public function testIndexNotDefined()
    {
        $exception = new ApiException('foo index not defined bar');

        $this->apiClient
            ->expects($this->any())
            ->method('get')->with($this->anything())
            ->willThrowException($exception);

        $this->expectException(IndexNotDefined::class);

        $this->query->getSnapshot();
    }
}

<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Exception;

use Kreait\Firebase\Database\Query;
use Kreait\Firebase\Exception\ApiException;
use Kreait\Firebase\Exception\QueryException;
use Kreait\Firebase\Tests\UnitTestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 */
class QueryExceptionTest extends UnitTestCase
{
    /**
     * @var Query|\PHPUnit_Framework_MockObject_MockObject
     */
    private $query;

    /**
     * @var QueryException
     */
    private $exception;

    protected function setUp()
    {
        $this->query = $this->createMock(Query::class);
        $this->exception = new QueryException($this->query);
    }

    public function testItReturnsAQuery()
    {
        $this->assertSame($this->query, $this->exception->getQuery());
    }

    public function testItExplainsAMissingOrderBy()
    {
        $request = $this->createMock(RequestInterface::class);
        $apiException = new ApiException($request, 'Foo bar orderby must be defined bar foo');

        $e = QueryException::fromApiException($apiException, $this->query);

        $this->assertNotSame($apiException->getMessage(), $e->getMessage());
    }

    public function testItExplainsWrongOrderingParameters()
    {
        $request = $this->createMock(RequestInterface::class);
        $apiException = new ApiException($request, 'Foo bar key index passed non bar foo');

        $e = QueryException::fromApiException($apiException, $this->query);

        $this->assertNotSame($apiException->getMessage(), $e->getMessage());
    }
}

<?php


namespace Tests\Firebase\Exception;

use Firebase\Database\Query;
use Firebase\Exception\ApiException;
use Firebase\Exception\QueryException;
use Tests\FirebaseTestCase;

class QueryExceptionTest extends FirebaseTestCase
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
        $apiException = new ApiException('Foo bar orderby must be defined bar foo');

        $e = QueryException::fromApiException($apiException, $this->query);

        $this->assertNotSame($apiException->getMessage(), $e->getMessage());
    }

    public function testItExplainsWrongOrderingParameters()
    {
        $apiException = new ApiException('Foo bar key index passed non bar foo');

        $e = QueryException::fromApiException($apiException, $this->query);

        $this->assertNotSame($apiException->getMessage(), $e->getMessage());
    }

}

<?php


namespace Tests\Firebase\Exception;


use Firebase\Exception\ApiException;
use Tests\FirebaseTestCase;

class ApiExceptionTest extends FirebaseTestCase
{
    public function testItCanHaveADebugMessage()
    {
        $e = new ApiException();

        $this->assertFalse($e->hasDebugMessage());
        $this->assertEmpty($e->getDebugMessage());

        $e->setDebugMessage('foo');

        $this->assertTrue($e->hasDebugMessage());
        $this->assertSame('foo', $e->getDebugMessage());
    }
}

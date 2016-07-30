<?php


namespace Tests\Firebase\Database\Query\Filter;


use Firebase\Database\Query\Filter\EndAt;
use Firebase\Exception\InvalidArgumentException;
use GuzzleHttp\Psr7\Uri;
use Tests\FirebaseTestCase;

class EndAtTest extends FirebaseTestCase
{
    public function testCreateWithInvalidValue()
    {
        $this->expectException(InvalidArgumentException::class);

        new EndAt(null);
    }

    function testModifyUri()
    {
        $filter = new EndAt('value');

        $this->assertContains('endAt=value', (string) $filter->modifyUri(new Uri('http://domain.tld')));
    }
}

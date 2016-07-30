<?php


namespace Tests\Firebase\Database\Query\Filter;


use Firebase\Database\Query\Filter\StartAt;
use Firebase\Exception\InvalidArgumentException;
use GuzzleHttp\Psr7\Uri;
use Tests\FirebaseTestCase;

class StartAtTest extends FirebaseTestCase
{
    public function testCreateWithInvalidValue()
    {
        $this->expectException(InvalidArgumentException::class);

        new StartAt(null);
    }

    function testModifyUri()
    {
        $filter = new StartAt('value');

        $this->assertContains('startAt=value', (string) $filter->modifyUri(new Uri('http://domain.tld')));
    }
}

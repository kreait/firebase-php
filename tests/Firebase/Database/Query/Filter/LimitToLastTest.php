<?php


namespace Tests\Firebase\Database\Query\Filter;


use Firebase\Database\Query\Filter\LimitToLast;
use Firebase\Exception\InvalidArgumentException;
use GuzzleHttp\Psr7\Uri;
use Tests\FirebaseTestCase;

class LimitToLastTest extends FirebaseTestCase
{
    public function testCreateWithInvalidValue()
    {
        $this->expectException(InvalidArgumentException::class);

        new LimitToLast(0);
    }

    function testModifyUri()
    {
        $filter = new LimitToLast(3);

        $this->assertContains('limitToLast=3', (string) $filter->modifyUri(new Uri('http://domain.tld')));
    }
}

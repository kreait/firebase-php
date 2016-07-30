<?php


namespace Tests\Firebase\Database\Query\Filter;


use Firebase\Database\Query\Filter\Shallow;
use GuzzleHttp\Psr7\Uri;
use Tests\FirebaseTestCase;

class ShallowTest extends FirebaseTestCase
{
    function testModifyUri()
    {
        $filter = new Shallow;

        $this->assertContains('shallow=true', (string) $filter->modifyUri(new Uri('http://domain.tld')));
    }
}

<?php

namespace Tests\Firebase\Database\Query\Filter;

use Firebase\Database\Query\Filter\Shallow;
use GuzzleHttp\Psr7\Uri;
use Tests\FirebaseTestCase;

class ShallowTest extends FirebaseTestCase
{
    public function testModifyUri()
    {
        $filter = new Shallow;

        $this->assertContains('shallow=true', (string) $filter->modifyUri(new Uri('http://domain.tld')));
    }
}

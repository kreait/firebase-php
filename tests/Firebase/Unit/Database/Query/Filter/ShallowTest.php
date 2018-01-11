<?php

namespace Kreait\Tests\Firebase\Unit\Database\Query\Filter;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database\Query\Filter\Shallow;
use Kreait\Tests\Firebase\Unit\UnitTestCase;

class ShallowTest extends UnitTestCase
{
    public function testModifyUri()
    {
        $filter = new Shallow();

        $this->assertContains('shallow=true', (string) $filter->modifyUri(new Uri('http://domain.tld')));
    }
}

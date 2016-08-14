<?php

namespace Tests\Firebase\Database\Query\Filter;

use Firebase\Database\Query\Filter\LimitToFirst;
use Firebase\Exception\InvalidArgumentException;
use GuzzleHttp\Psr7\Uri;
use Tests\FirebaseTestCase;

class LimitToFirstTest extends FirebaseTestCase
{
    public function testCreateWithInvalidValue()
    {
        $this->expectException(InvalidArgumentException::class);

        new LimitToFirst(0);
    }

    public function testModifyUri()
    {
        $filter = new LimitToFirst(3);

        $this->assertContains('limitToFirst=3', (string) $filter->modifyUri(new Uri('http://domain.tld')));
    }
}

<?php

namespace Tests\Firebase\Database\Query\Filter;

use Firebase\Database\Query\Filter\EqualTo;
use Firebase\Exception\InvalidArgumentException;
use GuzzleHttp\Psr7\Uri;
use Tests\FirebaseTestCase;

class EqualToTest extends FirebaseTestCase
{
    public function testCreateWithInvalidValue()
    {
        $this->expectException(InvalidArgumentException::class);

        new EqualTo(new \stdClass());
    }

    public function testModifyUri()
    {
        $filter = new EqualTo('value');

        $this->assertContains('equalTo=value', (string) $filter->modifyUri(new Uri('http://domain.tld')));
    }
}

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

    /**
     * @param $given
     * @param $expected
     *
     * @dataProvider valueProvider
     */
    public function testModifyUri($given, $expected)
    {
        $filter = new StartAt($given);

        $this->assertContains($expected, (string) $filter->modifyUri(new Uri('http://domain.tld')));
    }

    public function valueProvider()
    {
        return [
            [1, 'startAt=1'],
            ['value', 'startAt=%22value%22']
        ];
    }
}

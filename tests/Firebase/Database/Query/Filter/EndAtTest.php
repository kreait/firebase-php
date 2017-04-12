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

    /**
     * @param $given
     * @param $expected
     *
     * @dataProvider valueProvider
     */
    public function testModifyUri($given, $expected)
    {
        $filter = new EndAt($given);

        $this->assertContains($expected, (string) $filter->modifyUri(new Uri('http://domain.tld')));
    }

    public function valueProvider()
    {
        return [
            [1, 'endAt=1'],
            ['value', 'endAt=%22value%22']
        ];
    }
}

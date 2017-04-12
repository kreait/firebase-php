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

    /**
     * @param $given
     * @param $expected
     *
     * @dataProvider valueProvider
     */
    public function testModifyUri($given, $expected)
    {
        $filter = new EqualTo($given);

        $this->assertContains($expected, (string) $filter->modifyUri(new Uri('http://domain.tld')));
    }

    public function valueProvider()
    {
        return [
            [1, 'equalTo=1'],
            ['value', 'equalTo=%22value%22']
        ];
    }
}

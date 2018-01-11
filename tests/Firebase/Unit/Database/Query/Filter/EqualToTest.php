<?php

namespace Kreait\Tests\Firebase\Unit\Database\Query\Filter;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database\Query\Filter\EqualTo;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Tests\Firebase\Unit\UnitTestCase;

class EqualToTest extends UnitTestCase
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
            ['value', 'equalTo=%22value%22'],
        ];
    }
}

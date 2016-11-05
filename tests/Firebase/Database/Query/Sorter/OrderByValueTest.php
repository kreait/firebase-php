<?php

namespace Tests\Firebase\Database\Query\Sorter;

use Firebase\Database\Query\Sorter\OrderByValue;
use GuzzleHttp\Psr7\Uri;
use Tests\FirebaseTestCase;

class OrderByValueTest extends FirebaseTestCase
{
    /**
     * @var OrderByValue
     */
    protected $sorter;

    protected function setUp()
    {
        $this->sorter = new OrderByValue();
    }

    public function testModifyUri()
    {
        $this->assertContains(
            'orderBy='.rawurlencode('"$value"'),
            (string) $this->sorter->modifyUri(new Uri('http://domain.tld'))
        );
    }

    /**
     * @dataProvider valueProvider
     */
    public function testModifyValue($expected, $value)
    {
        $this->assertSame($expected, $this->sorter->modifyValue($value));
    }

    public function valueProvider()
    {
        return [
            'scalar' => [
                'expected' => 'scalar',
                'given' => 'scalar',
            ],
            'array' => [
                'expected' => [
                    'third' => 1,
                    'fourth' => 2,
                    'first' => 3,
                    'second' => 4,
                ],
                'given' => [
                    'first' => 3,
                    'second' => 4,
                    'third' => 1,
                    'fourth' => 2,
                ],
            ],
        ];
    }
}

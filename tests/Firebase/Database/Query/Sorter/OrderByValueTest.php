<?php


namespace Tests\Firebase\Database\Query\Sorter;

use Firebase\Database\Query\Sorter\OrderByChild;
use Firebase\Database\Query\Sorter\OrderByKey;
use Firebase\Database\Query\Sorter\OrderByValue;
use GuzzleHttp\Psr7\Uri;
use Tests\FirebaseTestCase;

class OrderByValueTest extends FirebaseTestCase
{
    /**
     * @var OrderByValue
     */
    protected $ascending;

    /**
     * @var OrderByValue
     */
    protected $descending;

    protected function setUp()
    {
        $this->ascending = new OrderByValue();
        $this->descending = new OrderByValue(SORT_DESC);
    }

    public function testModifyUri()
    {
        $this->assertContains(
            'orderBy='.rawurlencode('"$value"'),
            (string) $this->ascending->modifyUri(new Uri('http://domain.tld'))
        );
    }

    /**
     * @dataProvider sortAscendingValueProvider
     */
    public function testSortAscending($expected, $value)
    {
        $this->assertSame($expected, $this->ascending->modifyValue($value));
    }

    /**
     * @dataProvider sortDescendingValueProvider
     */
    public function testSortDescending($expected, $value)
    {
        $this->assertSame($expected, $this->descending->modifyValue($value));
    }

    public function sortAscendingValueProvider()
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
                ]
            ]
        ];
    }

    public function sortDescendingValueProvider()
    {
        return [
            'scalar' => [
                'expected' => 'scalar',
                'given' => 'scalar',
            ],
            'array' => [
                'expected' => [
                    'second' => 4,
                    'first' => 3,
                    'fourth' => 2,
                    'third' => 1,
                ],
                'given' => [
                    'first' => 3,
                    'second' => 4,
                    'third' => 1,
                    'fourth' => 2,
                ]
            ]
        ];
    }
}

<?php


namespace Tests\Firebase\Database\Query\Sorter;

use Firebase\Database\Query\Sorter\OrderByChild;
use Firebase\Database\Query\Sorter\OrderByKey;
use GuzzleHttp\Psr7\Uri;
use Tests\FirebaseTestCase;

class OrderByKeyTest extends FirebaseTestCase
{
    /**
     * @var OrderByKey
     */
    protected $ascending;

    /**
     * @var OrderByChild
     */
    protected $descending;

    protected function setUp()
    {
        $this->ascending = new OrderByKey();
        $this->descending = new OrderByKey(SORT_DESC);
    }

    public function testModifyUri()
    {
        $this->assertContains(
            'orderBy='.rawurlencode('"$key"'),
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
                    'a' => 'any',
                    'b' => 'any',
                    'c' => 'any',
                    'd' => 'any',
                ],
                'given' => [
                    'c' => 'any',
                    'a' => 'any',
                    'd' => 'any',
                    'b' => 'any',
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
                    'd' => 'any',
                    'c' => 'any',
                    'b' => 'any',
                    'a' => 'any',
                ],
                'given' => [
                    'c' => 'any',
                    'a' => 'any',
                    'd' => 'any',
                    'b' => 'any',
                ]
            ]
        ];
    }
}

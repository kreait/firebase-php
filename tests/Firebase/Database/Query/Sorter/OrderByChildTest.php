<?php

namespace Tests\Firebase\Database\Query\Sorter;

use Firebase\Database\Query\Sorter\OrderByChild;
use GuzzleHttp\Psr7\Uri;
use Tests\FirebaseTestCase;

class OrderByChildTest extends FirebaseTestCase
{
    /**
     * @var OrderByChild
     */
    protected $ascending;

    /**
     * @var OrderByChild
     */
    protected $descending;

    protected function setUp()
    {
        $this->ascending = new OrderByChild('key');
        $this->descending = new OrderByChild('key', SORT_DESC);
    }

    public function testModifyUri()
    {
        $this->assertContains(
            'orderBy='.rawurlencode('"key"'),
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
                    'third' => ['key' => 1],
                    'fourth' => ['key' => 2],
                    'first' => ['key' => 3],
                    'second' => ['key' => 4],
                ],
                'given' => [
                    'first' => ['key' => 3],
                    'second' => ['key' => 4],
                    'third' => ['key' => 1],
                    'fourth' => ['key' => 2],
                ],
            ],
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
                    'second' => ['key' => 4],
                    'first' => ['key' => 3],
                    'fourth' => ['key' => 2],
                    'third' => ['key' => 1],
                ],
                'given' => [
                    'first' => ['key' => 3],
                    'second' => ['key' => 4],
                    'third' => ['key' => 1],
                    'fourth' => ['key' => 2],
                ],
            ],
        ];
    }
}

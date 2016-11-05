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
    protected $sorter;

    protected function setUp()
    {
        $this->sorter = new OrderByChild('key');
    }

    public function testModifyUri()
    {
        $this->assertContains(
            'orderBy='.rawurlencode('"key"'),
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
}

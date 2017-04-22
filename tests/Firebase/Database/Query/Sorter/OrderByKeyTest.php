<?php

namespace Kreait\Tests\Firebase\Database\Query\Sorter;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database\Query\Sorter\OrderByKey;
use Kreait\Tests\FirebaseTestCase;

class OrderByKeyTest extends FirebaseTestCase
{
    /**
     * @var OrderByKey
     */
    protected $sorter;

    protected function setUp()
    {
        $this->sorter = new OrderByKey();
    }

    public function testModifyUri()
    {
        $this->assertContains(
            'orderBy='.rawurlencode('"$key"'),
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
                ],
            ],
        ];
    }
}

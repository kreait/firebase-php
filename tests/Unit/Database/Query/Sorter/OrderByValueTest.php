<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database\Query\Sorter;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database\Query\Sorter\OrderByValue;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
final class OrderByValueTest extends UnitTestCase
{
    protected OrderByValue $sorter;

    protected function setUp(): void
    {
        $this->sorter = new OrderByValue();
    }

    public function testModifyUri(): void
    {
        $this->assertStringContainsString(
            'orderBy='.\rawurlencode('"$value"'),
            (string) $this->sorter->modifyUri(new Uri('http://domain.tld'))
        );
    }

    /**
     * @dataProvider valueProvider
     *
     * @param mixed $expected
     * @param mixed $value
     */
    public function testModifyValue($expected, $value): void
    {
        $this->assertSame($expected, $this->sorter->modifyValue($value));
    }

    /**
     * @return array<string, array<string, mixed>>
     */
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

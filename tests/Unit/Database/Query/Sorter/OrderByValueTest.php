<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database\Query\Sorter;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database\Query\Sorter\OrderByValue;
use Kreait\Firebase\Tests\UnitTestCase;

use function rawurlencode;

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
            'orderBy='.rawurlencode('"$value"'),
            (string) $this->sorter->modifyUri(new Uri('http://domain.tld')),
        );
    }

    /**
     * @dataProvider valueProvider
     */
    public function testModifyValue(mixed $expected, mixed $value): void
    {
        $this->assertSame($expected, $this->sorter->modifyValue($value));
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function valueProvider()
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

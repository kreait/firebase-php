<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database\Query\Sorter;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database\Query\Sorter\OrderByValue;
use Kreait\Firebase\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

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

    #[Test]
    public function modifyUri(): void
    {
        $this->assertStringContainsString(
            'orderBy='.rawurlencode('"$value"'),
            (string) $this->sorter->modifyUri(new Uri('http://domain.example')),
        );
    }

    #[DataProvider('valueProvider')]
    #[Test]
    public function modifyValue(mixed $expected, mixed $value): void
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

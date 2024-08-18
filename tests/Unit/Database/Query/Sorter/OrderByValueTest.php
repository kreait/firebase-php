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
            (string) $this->sorter->modifyUri(new Uri('http://example.com')),
        );
    }

    #[DataProvider('valueProvider')]
    #[Test]
    public function modifyValue(mixed $expected, mixed $given): void
    {
        $this->assertSame($expected, $this->sorter->modifyValue($given));
    }

    public static function valueProvider(): \Iterator
    {
        yield 'scalar' => [
            'expected' => 'scalar',
            'given' => 'scalar',
        ];
        yield 'array' => [
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
        ];
    }
}

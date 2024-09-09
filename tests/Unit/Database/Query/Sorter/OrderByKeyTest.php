<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database\Query\Sorter;

use GuzzleHttp\Psr7\Uri;
use Iterator;
use Kreait\Firebase\Database\Query\Sorter\OrderByKey;
use Kreait\Firebase\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

use function rawurlencode;

/**
 * @internal
 */
final class OrderByKeyTest extends UnitTestCase
{
    private OrderByKey $sorter;

    protected function setUp(): void
    {
        $this->sorter = new OrderByKey();
    }

    #[Test]
    public function modifyUri(): void
    {
        $this->assertStringContainsString(
            'orderBy='.rawurlencode('"$key"'),
            (string) $this->sorter->modifyUri(new Uri('http://example.com')),
        );
    }

    #[DataProvider('valueProvider')]
    #[Test]
    public function modifyValue(mixed $expected, mixed $given): void
    {
        $this->assertSame($expected, $this->sorter->modifyValue($given));
    }

    public static function valueProvider(): Iterator
    {
        yield 'scalar' => [
            'expected' => 'scalar',
            'given' => 'scalar',
        ];
        yield 'array' => [
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
        ];
    }
}

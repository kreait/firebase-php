<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database\Query\Sorter;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database\Query\Sorter\OrderByKey;
use Kreait\Firebase\Tests\UnitTestCase;

use function rawurlencode;

/**
 * @internal
 */
final class OrderByKeyTest extends UnitTestCase
{
    protected OrderByKey $sorter;

    protected function setUp(): void
    {
        $this->sorter = new OrderByKey();
    }

    /**
     * @test
     */
    public function modifyUri(): void
    {
        $this->assertStringContainsString(
            'orderBy='.rawurlencode('"$key"'),
            (string) $this->sorter->modifyUri(new Uri('http://domain.tld')),
        );
    }

    /**
     * @dataProvider valueProvider
     *
     * @test
     */
    public function modifyValue(mixed $expected, mixed $value): void
    {
        $this->assertSame($expected, $this->sorter->modifyValue($value));
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function valueProvider(): array
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

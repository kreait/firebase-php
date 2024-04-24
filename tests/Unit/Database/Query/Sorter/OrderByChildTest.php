<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database\Query\Sorter;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database\Query\Sorter\OrderByChild;
use Kreait\Firebase\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

use function rawurlencode;
use function sprintf;

/**
 * @internal
 */
final class OrderByChildTest extends UnitTestCase
{
    #[DataProvider('valueProvider')]
    #[Test]
    public function orderByChild(string $childKey, mixed $expected, mixed $given): void
    {
        $sut = new OrderByChild($childKey);

        $this->assertStringContainsString(
            'orderBy='.rawurlencode(sprintf('"%s"', $childKey)),
            (string) $sut->modifyUri(new Uri('http://example.com')),
        );

        $this->assertSame($expected, $sut->modifyValue($given));
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function valueProvider(): array
    {
        return [
            'scalar' => [
                'childKey' => 'key',
                'expected' => 'scalar',
                'given' => 'scalar',
            ],
            'array' => [
                'childKey' => 'key',
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
            'nested' => [
                'childKey' => 'child/grandchild',
                'expected' => [
                    'third' => ['child' => ['grandchild' => 1]],
                    'fourth' => ['child' => ['grandchild' => 2]],
                    'first' => ['child' => ['grandchild' => 3]],
                    'second' => ['child' => ['grandchild' => 4]],
                ],
                'given' => [
                    'first' => ['child' => ['grandchild' => 3]],
                    'second' => ['child' => ['grandchild' => 4]],
                    'third' => ['child' => ['grandchild' => 1]],
                    'fourth' => ['child' => ['grandchild' => 2]],
                ],
            ],
            'super_nested' => [
                'childKey' => 'child/grandchild/great_grandchild',
                'expected' => [
                    'third' => ['child' => ['grandchild' => ['great_grandchild' => 1]]],
                    'fourth' => ['child' => ['grandchild' => ['great_grandchild' => 2]]],
                    'first' => ['child' => ['grandchild' => ['great_grandchild' => 3]]],
                    'second' => ['child' => ['grandchild' => ['great_grandchild' => 4]]],
                ],
                'given' => [
                    'first' => ['child' => ['grandchild' => ['great_grandchild' => 3]]],
                    'second' => ['child' => ['grandchild' => ['great_grandchild' => 4]]],
                    'third' => ['child' => ['grandchild' => ['great_grandchild' => 1]]],
                    'fourth' => ['child' => ['grandchild' => ['great_grandchild' => 2]]],
                ],
            ],
        ];
    }
}

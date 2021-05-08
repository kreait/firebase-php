<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database\Query\Sorter;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database\Query\Sorter\OrderByChild;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
class OrderByChildTest extends UnitTestCase
{
    /**
     * @dataProvider valueProvider
     *
     * @param mixed $expected
     * @param mixed $given
     */
    public function testOrderByChild(string $childKey, $expected, $given): void
    {
        $sut = new OrderByChild($childKey);

        $this->assertStringContainsString(
            'orderBy='.\rawurlencode(\sprintf('"%s"', $childKey)),
            (string) $sut->modifyUri(new Uri('http://domain.tld'))
        );

        $this->assertSame($expected, $sut->modifyValue($given));
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function valueProvider(): array
    {
        return [
            'scalar' => [
                'key' => 'key',
                'expected' => 'scalar',
                'given' => 'scalar',
            ],
            'array' => [
                'key' => 'key',
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
                'key' => 'child/grandchild',
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
                'key' => 'child/grandchild/great_grandchild',
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

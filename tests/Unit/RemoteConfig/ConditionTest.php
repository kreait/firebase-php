<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\RemoteConfig;

use Kreait\Firebase\RemoteConfig\Condition;
use Kreait\Firebase\RemoteConfig\TagColor;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
class ConditionTest extends UnitTestCase
{
    /**
     * @dataProvider valueProvider
     *
     * @param string|TagColor|null $tagColor
     */
    public function testCreateCondition(string $name, ?string $expression = null, $tagColor = null): void
    {
        $condition = Condition::named($name);

        if ($expression) {
            $condition = $condition->withExpression($expression);
        }

        if ($tagColor) {
            $condition = $condition->withTagColor($tagColor);
        }

        $this->assertSame($name, $condition->name());
        $expected = [
            'name' => $name,
            'expression' => $expression ?: 'false',
            'tagColor' => $tagColor ? (string) $tagColor : null,
        ];

        $this->assertEquals(\array_filter($expected), $condition->jsonSerialize());
        $this->assertEquals($condition, Condition::fromArray($expected));
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function valueProvider(): array
    {
        return [
            'color as string' => ['name', 'expression', TagColor::GREEN],
            'color as object' => ['name', 'expression', new TagColor(TagColor::ORANGE)],
            'no color' => ['name', 'expression', null],
            'no expression, no color' => ['name', null, null],
        ];
    }
}

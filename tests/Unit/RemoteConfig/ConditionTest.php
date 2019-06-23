<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\RemoteConfig;

use Kreait\Firebase\RemoteConfig\Condition;
use Kreait\Firebase\RemoteConfig\TagColor;
use Kreait\Firebase\Tests\UnitTestCase;

class ConditionTest extends UnitTestCase
{
    /**
     * @dataProvider valueProvider
     */
    public function testCreateCondition(string $name, string $expression = null, $tagColor = null)
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

    public function valueProvider()
    {
        return [
            ['name', 'expression', TagColor::GREEN],
            ['name', 'expression', new TagColor(TagColor::ORANGE)],
            ['name', 'expression', null],
            ['name', null, null],
        ];
    }
}

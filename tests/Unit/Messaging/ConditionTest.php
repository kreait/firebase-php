<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Kreait\Firebase\Exception\Messaging\InvalidArgument;
use Kreait\Firebase\Messaging\Condition;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ConditionTest extends TestCase
{
    /**
     * @dataProvider valueProvider
     */
    public function testFromValue($expected, $value)
    {
        $this->assertSame($expected, Condition::fromValue($value)->value());
    }

    /**
     * @dataProvider invalidValueProvider
     */
    public function testFromInvalidValue($value)
    {
        $this->expectException(InvalidArgument::class);
        Condition::fromValue($value);
    }

    public function testNoMoreThanFiveTopics()
    {
        $valid = "'a' in topics && 'b' in topics || 'c' in topics || 'd' in topics || 'e' in topics";
        $invalid = $valid." || 'f' in topics";

        Condition::fromValue($valid);
        $this->addToAssertionCount(1);

        $this->expectException(InvalidArgument::class);
        Condition::fromValue($invalid);
    }

    public function valueProvider()
    {
        return [
            ["'dogs' in topics || 'cats' in topics", "'dogs' in topics || 'cats' in topics"],
            ["'dogs' in topics || 'cats' in topics", '"dogs" in topics || "cats" in topics'],
        ];
    }

    public function invalidValueProvider()
    {
        return [
            ["'dogs in Topics"],
            ["'dogs in Topics || 'cats' in topics"],
        ];
    }
}

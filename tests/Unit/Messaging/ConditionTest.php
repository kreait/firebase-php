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
    public function testFromValue(string $expected, string $value): void
    {
        $this->assertSame($expected, Condition::fromValue($value)->value());
    }

    /**
     * @dataProvider invalidValueProvider
     */
    public function testFromInvalidValue(string $value): void
    {
        $this->expectException(InvalidArgument::class);
        Condition::fromValue($value);
    }

    public function testNoMoreThanFiveTopics(): void
    {
        $valid = "'a' in topics && 'b' in topics || 'c' in topics || 'd' in topics || 'e' in topics";
        $invalid = $valid." || 'f' in topics";

        Condition::fromValue($valid);
        $this->addToAssertionCount(1);

        $this->expectException(InvalidArgument::class);
        Condition::fromValue($invalid);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function valueProvider(): array
    {
        return [
            'single quotes' => ["'dogs' in topics || 'cats' in topics", "'dogs' in topics || 'cats' in topics"],
            'double quotes' => ["'dogs' in topics || 'cats' in topics", '"dogs" in topics || "cats" in topics'],
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function invalidValueProvider(): array
    {
        return [
            'single quotes' => ["'dogs in Topics"],
            'double quotes' => ["'dogs in Topics || 'cats' in topics"],
        ];
    }
}

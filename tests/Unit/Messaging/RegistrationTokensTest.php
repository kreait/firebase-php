<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use InvalidArgumentException;
use Kreait\Firebase\Messaging\RegistrationToken;
use Kreait\Firebase\Messaging\RegistrationTokens;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 */
final class RegistrationTokensTest extends TestCase
{
    /**
     * @dataProvider validValuesWithExpectedCounts
     *
     * @param mixed $value
     */
    public function testItCanBeCreatedFromValues(int $expectedCount, $value): void
    {
        $tokens = RegistrationTokens::fromValue($value);

        $this->assertCount($expectedCount, $tokens);
        $this->assertSame(!$expectedCount, $tokens->isEmpty());
    }

    /**
     * @dataProvider invalidValues
     *
     * @param mixed $value
     */
    public function testItRejectsInvalidValues($value): void
    {
        $this->expectException(InvalidArgumentException::class);
        RegistrationTokens::fromValue($value);
    }

    public function testItReturnsStrings(): void
    {
        $token = RegistrationToken::fromValue('foo');

        $tokens = RegistrationTokens::fromValue([$token, $token]);
        $this->assertEquals(['foo', 'foo'], $tokens->asStrings());
    }

    /**
     * @return array<string, array<int, int|mixed>>
     */
    public function validValuesWithExpectedCounts(): array
    {
        $foo = RegistrationToken::fromValue('foo');

        return [
            'string' => [1, 'foo'],
            'token object' => [1, $foo],
            'collection' => [2, new RegistrationTokens($foo, $foo)],
            'array with mixed values' => [2, [$foo, 'bar']],
            'array with an invalid value' => [2, [$foo, new stdClass(), 'bar']],
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function invalidValues(): array
    {
        return [
            'invalid object' => [new stdClass()],
        ];
    }
}

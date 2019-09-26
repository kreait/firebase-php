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
    const IS_EMPTY = true;
    const IS_NOT_EMPTY = false;

    /**
     * @test
     * @dataProvider validValuesWithExpectedCounts
     */
    public function it_can_be_created_from_values($expectedCount, $value)
    {
        $tokens = RegistrationTokens::fromValue($value);

        $this->assertCount($expectedCount, $tokens);
        $this->assertSame(!$expectedCount, $tokens->isEmpty());
    }

    /**
     * @test
     * @dataProvider invalidValues
     */
    public function it_rejects_invalid_values($value)
    {
        $this->expectException(InvalidArgumentException::class);
        RegistrationTokens::fromValue($value);
    }

    /** @test */
    public function it_returns_strings()
    {
        $token = RegistrationToken::fromValue('foo');

        $tokens = RegistrationTokens::fromValue([$token, $token]);
        $this->assertEquals(['foo', 'foo'], $tokens->asStrings());
    }

    public function validValuesWithExpectedCounts()
    {
        $foo = RegistrationToken::fromValue('foo');

        return [
            [1, 'foo'],
            [1, $foo],
            [2, new RegistrationTokens($foo, $foo)],
            [2, [$foo, 'bar']],
            [2, [$foo, new stdClass(), 'bar']],
        ];
    }

    public function invalidValues()
    {
        return [
            [new stdClass()],
        ];
    }
}

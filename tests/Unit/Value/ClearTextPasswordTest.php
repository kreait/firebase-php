<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Value;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Value\ClearTextPassword;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ClearTextPasswordTest extends TestCase
{
    /**
     * @dataProvider validValues
     *
     * @param mixed $value
     */
    public function testWithValidValue($value): void
    {
        $password = ClearTextPassword::fromString($value)->value;

        $this->assertSame($value, $password);
    }

    /**
     * @dataProvider invalidValues
     *
     * @param mixed $value
     */
    public function testWithInvalidValue($value): void
    {
        $this->expectException(InvalidArgumentException::class);
        ClearTextPassword::fromString($value);
    }

    /**
     * @return array<string, array<string>>
     */
    public function validValues(): array
    {
        return [
            'long enough' => ['long enough'],
        ];
    }

    /**
     * @return array<string, array<string>>
     */
    public function invalidValues(): array
    {
        return [
            'empty string' => [''],
            'less than 6 chars' => ['short'],
        ];
    }
}

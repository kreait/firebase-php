<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Value;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Value\ClearTextPassword;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ClearTextPasswordTest extends TestCase
{
    /**
     * @dataProvider validValues
     *
     * @param mixed $value
     */
    public function testWithValidValue($value): void
    {
        $password = new ClearTextPassword($value);

        $this->assertSame($value, (string) $password);
        $this->assertSame($value, $password->jsonSerialize());
        $this->assertTrue($password->equalsTo($value));
    }

    /**
     * @dataProvider invalidValues
     *
     * @param mixed $value
     */
    public function testWithInvalidValue($value): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ClearTextPassword($value);
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

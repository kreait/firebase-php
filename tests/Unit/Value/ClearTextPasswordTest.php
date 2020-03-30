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
     */
    public function testWithInvalidValue($value): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ClearTextPassword($value);
    }

    public function validValues(): array
    {
        return [
            ['longenough'],
        ];
    }

    public function invalidValues(): array
    {
        return [
            [''],
            ['short'],
        ];
    }
}

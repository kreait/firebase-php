<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Value;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Value\PhoneNumber;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class PhoneNumberTest extends TestCase
{
    /**
     * @dataProvider validValues
     */
    public function testWithValidValue(string $value): void
    {
        $phoneNumber = new PhoneNumber($value);

        $this->assertSame($value, (string) $phoneNumber);
        $this->assertSame($value, $phoneNumber->jsonSerialize());
        $this->assertTrue($phoneNumber->equalsTo($value));
    }

    /**
     * @dataProvider invalidValues
     */
    public function testWithInvalidValue(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PhoneNumber($value);
    }

    /**
     * @return array<string, array<string>>
     */
    public function validValues(): array
    {
        return [
            'valid' => ['+123456789'],
        ];
    }

    /**
     * @return array<string, array<string>>
     */
    public function invalidValues(): array
    {
        return [
            'empty string' => [''],
            'not a phone number' => ['notaphonenumber'],
            'no region code' => ['12345678'],
        ];
    }
}

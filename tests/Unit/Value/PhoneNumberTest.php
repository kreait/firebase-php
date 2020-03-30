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
    public function testWithValidValue($value): void
    {
        $phoneNumber = new PhoneNumber($value);

        $this->assertSame($value, (string) $phoneNumber);
        $this->assertSame($value, $phoneNumber->jsonSerialize());
        $this->assertTrue($phoneNumber->equalsTo($value));
    }

    /**
     * @dataProvider invalidValues
     */
    public function testWithInvalidValue($value): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PhoneNumber($value);
    }

    public function validValues(): array
    {
        return [
            ['+123456789'],
        ];
    }

    public function invalidValues(): array
    {
        return [
            [''],
            ['nonumber'],
            'no_region_code' => ['12345678'],
        ];
    }
}

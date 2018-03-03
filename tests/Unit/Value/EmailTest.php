<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Value;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Value\Email;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    /**
     * @dataProvider validValues
     */
    public function testWithValidValue($value)
    {
        $email = new Email($value);

        $this->assertSame($value, (string) $email);
        $this->assertSame($value, $email->jsonSerialize());
        $this->assertTrue($email->equalsTo($value));
    }

    /**
     * @dataProvider invalidValues
     */
    public function testWithInvalidValue($value)
    {
        $this->expectException(InvalidArgumentException::class);
        new EMail($value);
    }

    public function validValues(): array
    {
        return [
            ['user@domain.tld'],
        ];
    }

    public function invalidValues(): array
    {
        return [
            [''],
            ['invalid'],
        ];
    }
}

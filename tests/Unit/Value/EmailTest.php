<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Value;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Value\Email;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class EmailTest extends TestCase
{
    /**
     * @dataProvider validValues
     *
     * @test
     */
    public function withValidValue(string $value): void
    {
        $email = Email::fromString($value)->value;

        $this->assertSame($value, $email);
    }

    /**
     * @dataProvider invalidValues
     *
     * @test
     */
    public function withInvalidValue(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        Email::fromString($value);
    }

    /**
     * @return array<string, array<string>>
     */
    public static function validValues(): array
    {
        return [
            'user@domain.tld' => ['user@domain.tld'],
        ];
    }

    /**
     * @return array<string, array<string>>
     */
    public static function invalidValues(): array
    {
        return [
            'empty string' => [''],
            'invalid' => ['invalid'],
        ];
    }
}

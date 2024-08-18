<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Value;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Value\Email;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class EmailTest extends TestCase
{
    #[DataProvider('validValues')]
    #[Test]
    public function withValidValue(string $value): void
    {
        $email = Email::fromString($value)->value;

        $this->assertSame($value, $email);
    }

    #[DataProvider('invalidValues')]
    #[Test]
    public function withInvalidValue(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        Email::fromString($value);
    }

    public static function validValues(): \Iterator
    {
        yield 'user@example.com' => ['user@example.com'];
    }

    public static function invalidValues(): \Iterator
    {
        yield 'empty string' => [''];
        yield 'invalid' => ['invalid'];
    }
}

<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Value;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Value\ClearTextPassword;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ClearTextPasswordTest extends TestCase
{
    #[DataProvider('validValues')]
    #[Test]
    public function withValidValue(mixed $value): void
    {
        $password = ClearTextPassword::fromString($value)->value;

        $this->assertSame($value, $password);
    }

    #[DataProvider('invalidValues')]
    #[Test]
    public function withInvalidValue(mixed $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        ClearTextPassword::fromString($value);
    }

    /**
     * @return array<string, array<string>>
     */
    public static function validValues(): array
    {
        return [
            'long enough' => ['long enough'],
        ];
    }

    /**
     * @return array<string, array<string>>
     */
    public static function invalidValues(): array
    {
        return [
            'empty string' => [''],
            'less than 6 chars' => ['short'],
        ];
    }
}

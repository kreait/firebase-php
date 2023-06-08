<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Util;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use Kreait\Firebase\Util\DT;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

use function microtime;
use function time;

/**
 * @internal
 */
final class DTTest extends TestCase
{
    #[DataProvider('validFixedValues')]
    #[Test]
    public function convertWithFixedValues(string $expected, mixed $value): void
    {
        $dt = DT::toUTCDateTimeImmutable($value);

        $this->assertSame($expected, $dt->format('U.u'));
        $this->assertSame('UTC', $dt->getTimezone()->getName());
    }

    #[DataProvider('validVariableValues')]
    #[Test]
    public function convertWithVariableValues(mixed $value): void
    {
        $dt = DT::toUTCDateTimeImmutable($value);

        $this->assertSame('UTC', $dt->getTimezone()->getName());
    }

    #[DataProvider('invalidValues')]
    #[Test]
    public function convertInvalid(mixed $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        DT::toUTCDateTimeImmutable($value);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function validFixedValues(): array
    {
        return [
            'seconds' => ['1234567890.000000', 1_234_567_890],
            'milliseconds_1' => ['1234567890.000000', 1_234_567_890_000],
            'milliseconds_2' => ['1234567890.123000', 1_234_567_890_123],
            'date_string' => ['345254400.000000', '10.12.1980'],
            'timezone_1' => ['345328496.789012', '10.12.1980 12:34:56.789012 -08:00'],
            'timezone_2' => ['345328496.789012', new DateTimeImmutable('10.12.1980 12:34:56.789012', new DateTimeZone('America/Los_Angeles'))],
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function validVariableValues(): array
    {
        return [
            'null' => [null],
            'zero' => [0],
            'zero_as_string' => ['0'],
            'true' => [true],
            'false' => [false],
            'microtime' => [microtime()],
            'time' => [time()],
            'now in LA' => [new DateTimeImmutable('now', new DateTimeZone('America/Los_Angeles'))],
            'now in Bangkok' => [new DateTimeImmutable('now', new DateTimeZone('Asia/Bangkok'))],
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function invalidValues(): array
    {
        return [
            'string' => ['foo'],
            'object' => [new stdClass()],
        ];
    }
}

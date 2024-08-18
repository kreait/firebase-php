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

    public static function validFixedValues(): \Iterator
    {
        yield 'seconds' => ['1234567890.000000', 1_234_567_890];
        yield 'milliseconds_1' => ['1234567890.000000', 1_234_567_890_000];
        yield 'milliseconds_2' => ['1234567890.123000', 1_234_567_890_123];
        yield 'date_string' => ['345254400.000000', '10.12.1980'];
        yield 'timezone_1' => ['345328496.789012', '10.12.1980 12:34:56.789012 -08:00'];
        yield 'timezone_2' => ['345328496.789012', new DateTimeImmutable('10.12.1980 12:34:56.789012', new DateTimeZone('America/Los_Angeles'))];
    }

    public static function validVariableValues(): \Iterator
    {
        yield 'null' => [null];
        yield 'zero' => [0];
        yield 'zero_as_string' => ['0'];
        yield 'true' => [true];
        yield 'false' => [false];
        yield 'microtime' => [microtime()];
        yield 'time' => [time()];
        yield 'now in LA' => [new DateTimeImmutable('now', new DateTimeZone('America/Los_Angeles'))];
        yield 'now in Bangkok' => [new DateTimeImmutable('now', new DateTimeZone('Asia/Bangkok'))];
    }

    public static function invalidValues(): \Iterator
    {
        yield 'string' => ['foo'];
        yield 'object' => [new stdClass()];
    }
}

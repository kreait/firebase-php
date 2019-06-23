<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Util;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Kreait\Firebase\Util\DT;
use PHPUnit\Framework\TestCase;

class DTTest extends TestCase
{
    /**
     * @dataProvider validFixedValues
     */
    public function testConvertWithFixedValues($expected, $value)
    {
        $dt = DT::toUTCDateTimeImmutable($value);

        $this->assertEquals($expected, $dt->format('U.u'));
        $this->assertEquals('UTC', $dt->getTimezone()->getName());
    }

    /**
     * @dataProvider validVariableValues
     */
    public function testConvertWithVariableValues($value)
    {
        $dt = DT::toUTCDateTimeImmutable($value);

        $this->assertInstanceOf(DateTimeImmutable::class, $dt);
        $this->assertEquals('UTC', $dt->getTimezone()->getName());
    }

    /**
     * @dataProvider invalidValues
     */
    public function testConvertInvalid($value)
    {
        $this->expectException(\InvalidArgumentException::class);

        DT::toUTCDateTimeImmutable($value);
    }

    public function validFixedValues()
    {
        return [
            'seconds' => ['1234567890.000000', 1234567890],
            'milliseconds_1' => ['1234567890.000000', 1234567890000],
            'milliseconds_2' => ['1234567890.123000', 1234567890123],
            'date_string' => ['345254400.000000', '10.12.1980'],
            'timezoned_1' => ['345328496.789012', '10.12.1980 12:34:56.789012 -08:00'],
            'timezoned_2' => ['345328496.789012', new DateTime('10.12.1980 12:34:56.789012', new DateTimeZone('America/Los_Angeles'))],
        ];
    }

    public function validVariableValues()
    {
        return [
            [\microtime()],
            [\time()],
            [new DateTime('now', new DateTimeZone('America/Los_Angeles'))],
            [new DateTimeImmutable('now', new DateTimeZone('Asia/Bangkok'))],
        ];
    }

    public function invalidValues()
    {
        return [
            ['foo'],
        ];
    }
}

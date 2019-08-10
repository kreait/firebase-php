<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Clock;

use DateTimeImmutable;
use DateTimeZone;
use Kreait\Firebase\Clock\SystemClock;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class SystemClockTest extends TestCase
{
    /** @test */
    public function it_uses_the_default_timezone()
    {
        $timezoneBackup = \date_default_timezone_get();

        \date_default_timezone_set('UTC');

        $defaultClock = new SystemClock();
        $utcClock = new SystemClock(new DateTimeZone('UTC'));

        $this->assertEquals($defaultClock, $utcClock);

        \date_default_timezone_set($timezoneBackup);
    }

    /** @test */
    public function it_uses_a_provided_timezone()
    {
        $timezone = new DateTimeZone('Asia/Bangkok');
        $clock = new SystemClock($timezone);

        $earlier = new DateTimeImmutable('now', $timezone);
        $now = $clock->now();
        $later = new DateTimeImmutable('now', $timezone);

        $this->assertEquals($timezone, $now->getTimezone());
        $this->assertGreaterThanOrEqual($now, $later);
        $this->assertLessThanOrEqual($now, $earlier);
    }
}

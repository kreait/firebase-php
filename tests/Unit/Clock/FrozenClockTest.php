<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Clock;

use DateTimeImmutable;
use Kreait\Firebase\Clock\FrozenClock;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class FrozenClockTest extends TestCase
{
    /** @test */
    public function it_works()
    {
        $now = new DateTimeImmutable();
        $then = $now->modify('+'.\random_int(1, 10).' seconds');

        $clock = new FrozenClock($now);
        $this->assertSame($now, $clock->now());

        $clock->setTo($then);
        $this->assertNotSame($now, $clock->now());
        $this->assertSame($then, $clock->now());
    }
}

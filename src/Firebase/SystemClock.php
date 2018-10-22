<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use DateTimeImmutable;
use DateTimeZone;

final class SystemClock implements Clock
{
    /**
     * @var DateTimeZone
     */
    private $timeZone;

    public function __construct(DateTimeZone $timeZone = null)
    {
        $this->timeZone = $timeZone ?: new DateTimeZone(date_default_timezone_get());
    }

    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', $this->timeZone);
    }
}

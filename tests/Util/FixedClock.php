<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Util;

use DateTimeImmutable;
use DateTimeInterface;
use Kreait\Firebase\Clock;

class FixedClock implements Clock
{
    /**
     * @var DateTimeInterface
     */
    private $fixedTime;

    public function __construct(DateTimeInterface $fixedTime)
    {
        $this->fixedTime = $fixedTime;
    }

    public function now(): DateTimeImmutable
    {
        return DateTimeImmutable::createFromFormat(\DATE_ATOM, $this->fixedTime->format(\DATE_ATOM));
    }
}

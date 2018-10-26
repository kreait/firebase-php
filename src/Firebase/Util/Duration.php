<?php

declare(strict_types=1);

namespace Kreait\Firebase\Util;

use DateInterval;
use DateTimeImmutable;

final class Duration
{
    /**
     * @var DateInterval
     */
    private $interval;

    private function __construct(DateInterval $interval)
    {
        $this->interval = $interval;
    }

    public static function fromValue($value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        if ($value instanceof DateInterval) {
            return new self($value);
        }

        if ($value instanceof \DateTimeInterface) {
            return self::fromStartToEnd(new DateTimeImmutable(), $value);
        }

        if (\is_int($value) || ctype_digit((string) $value)) {
            return new self(new DateInterval("PT{$value}S"));
        }

        return new self(DateInterval::createFromDateString((string) $value));
    }

    public static function fromStartToEnd(\DateTimeInterface $start, \DateTimeInterface $end): self
    {
        return new self($start->diff($end, true));
    }

    public function inSeconds(): int
    {
        $now = new DateTimeImmutable();
        $then = $now->add($this->interval);

        return $then->getTimestamp() - $now->getTimestamp();
    }

    public function asInterval(): DateInterval
    {
        return $this->interval;
    }

    public function compareTo(Duration $other): int
    {
        return $this->inSeconds() <=> $other->inSeconds();
    }

    public function isLongerThan($other): bool
    {
        $other = self::fromValue($other);

        return $this->compareTo($other) > 0;
    }

    public function isShorterThan($other): bool
    {
        $other = self::fromValue($other);

        return $this->compareTo($other) < 0;
    }

    public function equals($other): bool
    {
        $other = self::fromValue($other);

        return $this->compareTo($other) === 0;
    }

    public function isWithin($smaller, $larger): bool
    {
        $smaller = self::fromValue($smaller);
        $larger = self::fromValue($larger);

        $currentSeconds = $this->inSeconds();

        return ($smaller->inSeconds() <= $currentSeconds) && ($currentSeconds <= $larger->inSeconds());
    }
}

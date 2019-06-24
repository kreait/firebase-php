<?php

declare(strict_types=1);

namespace Kreait\Firebase\Util;

use DateTimeImmutable;
use DateTimeZone;
use Kreait\Firebase\Exception\InvalidArgumentException;

/**
 * @internal
 */
class DT
{
    /**
     * @internal
     *
     * @param mixed $value
     */
    public static function toUTCDateTimeImmutable($value): DateTimeImmutable
    {
        $tz = new DateTimeZone('UTC');
        $now = \time();

        if (\ctype_digit($value)) {
            // Seconds
            if (\mb_strlen((string) $value) === \mb_strlen((string) $now)) {
                return DateTimeImmutable::createFromFormat('U', (string) $value)
                    ->setTimezone($tz);
            }

            // Milliseconds
            if (\mb_strlen((string) $value) === \mb_strlen((string) ($now * 1000))) {
                return DateTimeImmutable::createFromFormat('U.u', \sprintf('%F', $value / 1000))
                    ->setTimezone($tz);
            }
        }

        if ($value instanceof \DateTimeInterface) {
            return DateTimeImmutable::createFromFormat('U.u', $value->format('U.u'))
                ->setTimezone($tz);
        }

        // microtime
        if (\preg_match('@(?P<msec>^0?\.\d+) (?P<sec>\d+)$@', $value, $matches)) {
            $value = (float) $matches['sec'] + (float) $matches['msec'];

            return DateTimeImmutable::createFromFormat('U.u', \sprintf('%F', $value))
                ->setTimezone($tz);
        }

        try {
            return (new DateTimeImmutable($value))->setTimezone($tz);
        } catch (\Throwable $e) {
            throw new InvalidArgumentException($e->getMessage());
        }
    }
}

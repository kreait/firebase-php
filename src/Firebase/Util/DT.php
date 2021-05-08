<?php

declare(strict_types=1);

namespace Kreait\Firebase\Util;

use DateTimeImmutable;
use DateTimeZone;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Throwable;

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

        if (
            ($value instanceof \DateTimeInterface)
            && $result = DateTimeImmutable::createFromFormat('U.u', $value->format('U.u'))
        ) {
            return $result->setTimezone($tz);
        }

        if ($value === null || $value === 0 || \is_bool($value)) {
            $value = '0';
        }

        if (\is_scalar($value) || (\is_object($value) && \method_exists($value, '__toString'))) {
            $value = (string) $value;
        } else {
            $type = \get_debug_type($value);

            throw new InvalidArgumentException("This {$type} cannot be parsed to a DateTime value");
        }

        if (\ctype_digit($value)) {
            // Seconds
            if ($value === '0' || \mb_strlen($value) === \mb_strlen((string) $now)) {
                if ($result = DateTimeImmutable::createFromFormat('U', $value)) {
                    return $result->setTimezone($tz);
                }
            }

            // Milliseconds
            if (
                (\mb_strlen($value) === \mb_strlen((string) ($now * 1000)))
                && $result = DateTimeImmutable::createFromFormat('U.u', \sprintf('%F', (float) ($value / 1000)))
            ) {
                return $result->setTimezone($tz);
            }
        }

        // microtime
        if (\preg_match('@(?P<msec>^0?\.\d+) (?P<sec>\d+)$@', $value, $matches)) {
            $value = (string) ((float) $matches['sec'] + (float) $matches['msec']);

            if ($result = DateTimeImmutable::createFromFormat('U.u', \sprintf('%F', $value))) {
                return $result->setTimezone($tz);
            }
        }

        try {
            return (new DateTimeImmutable($value))->setTimezone($tz);
        } catch (Throwable $e) {
            throw new InvalidArgumentException($e->getMessage());
        }
    }
}

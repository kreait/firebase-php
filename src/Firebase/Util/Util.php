<?php

declare(strict_types=1);

namespace Kreait\Firebase\Util;

use DateTimeImmutable;

class Util
{
    public static function parseTimestamp($timestamp): DateTimeImmutable
    {
        $dt = new DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $dt->setTimestamp(\intval($timestamp, 10));

        return $dt;
    }
}

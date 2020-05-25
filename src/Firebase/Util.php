<?php

declare(strict_types=1);

namespace Kreait\Firebase;

/**
 * @internal
 */
final class Util
{
    /**
     * @internal
     */
    public static function getenv(string $name): ?string
    {
        $value = $_SERVER[$name] ?? $_ENV[$name] ?? \getenv($name);

        if ($value !== false && $value !== null) {
            return (string) $value;
        }

        return null;
    }
}

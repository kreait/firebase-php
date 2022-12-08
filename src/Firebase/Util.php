<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use function getenv;
use function in_array;
use function putenv;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
final class Util
{
    /**
     * @param non-empty-string $name
     *
     * @return non-empty-string|null
     */
    public static function getenv(string $name): ?string
    {
        $value = trim((string) ($_SERVER[$name] ?? $_ENV[$name] ?? getenv($name)));

        return $value !== '' ? $value : null;
    }

    /**
     * @param non-empty-string $name
     * @param non-empty-string $value
     */
    public static function putenv(string $name, string $value): void
    {
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
        putenv("{$name}={$value}");
    }

    /**
     * @param non-empty-string $name
     */
    public static function rmenv(string $name): void
    {
        unset($_ENV[$name], $_SERVER[$name]);
        putenv($name);
    }

    /**
     * @return non-empty-string|null
     */
    public static function authEmulatorHost(): ?string
    {
        $emulatorHost = self::getenv('FIREBASE_AUTH_EMULATOR_HOST');

        if (!in_array($emulatorHost, [null, ''], true)) {
            return $emulatorHost;
        }

        return null;
    }

    /**
     * @return non-empty-string|null
     */
    public static function rtdbEmulatorHost(): ?string
    {
        $emulatorHost = self::getenv('FIREBASE_DATABASE_EMULATOR_HOST');

        if (!in_array($emulatorHost, [null, ''], true)) {
            return $emulatorHost;
        }

        return null;
    }
}

<?php

declare(strict_types=1);

namespace Kreait\Firebase;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
final class Util
{
    public static function getenv(string $name): ?string
    {
        $value = $_SERVER[$name] ?? $_ENV[$name] ?? \getenv($name);

        if ($value !== false && $value !== null) {
            return (string) $value;
        }

        return null;
    }

    public static function putenv(string $name, string $value): void
    {
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
        \putenv("{$name}={$value}");
    }

    public static function rmenv(string $name): void
    {
        unset($_ENV[$name], $_SERVER[$name]);
        \putenv($name);
    }

    public static function authEmulatorHost(): string
    {
        $emulatorHost = self::getenv('FIREBASE_AUTH_EMULATOR_HOST');

        if (!\in_array($emulatorHost, [null, ''], true)) {
            return $emulatorHost;
        }

        return '';
    }
}

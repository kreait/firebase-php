<?php

declare(strict_types=1);

namespace Kreait\Firebase\IdentityPlatform;

use Kreait\Firebase\Exception\InvalidArgumentException;

trait ParsesName
{
    /**
     * Parses name with possibke Project/Tenant.
     */
    public static function parseName(string $name): string
    {
        $parts = \explode('/', $name);
        $name = \end($parts);
        static::validateName($name);

        return $name;
    }

    /**
     * Returns Name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @internal
     *
     * @throws InvalidArgumentException
     */
    abstract public static function validateName(string $name): bool;
}

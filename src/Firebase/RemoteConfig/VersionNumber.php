<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use Kreait\Firebase\Exception\InvalidArgumentException;

final class VersionNumber implements \JsonSerializable
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @param int|string $value
     */
    public static function fromValue($value): self
    {
        $valueString = (string) $value;

        if (!\ctype_digit($valueString)) {
            throw new InvalidArgumentException('A version number should only consist of digits');
        }

        return new self($valueString);
    }

    public function __toString()
    {
        return $this->value;
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }

    /**
     * @param self|string $other
     */
    public function equalsTo($other): bool
    {
        return $this->value === (string) $other;
    }
}

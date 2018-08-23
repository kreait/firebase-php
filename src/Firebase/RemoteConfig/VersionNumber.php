<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Value;

final class VersionNumber implements Value, \JsonSerializable
{
    /**
     * @var string
     */
    private $value;

    private function __construct()
    {
    }

    public static function fromValue($value): self
    {
        $valueString = (string) $value;

        if (!ctype_digit($valueString)) {
            throw new InvalidArgumentException('A version number should only consist of digits');
        }

        $new = new self();
        $new->value = $valueString;

        return $new;
    }

    public function __toString()
    {
        return $this->value;
    }

    public function jsonSerialize()
    {
        return $this->value;
    }

    public function equalsTo($other): bool
    {
        return $this->value === (string) $other;
    }
}

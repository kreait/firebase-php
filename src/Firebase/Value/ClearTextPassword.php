<?php

declare(strict_types=1);

namespace Kreait\Firebase\Value;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Value;

class ClearTextPassword implements Value, \JsonSerializable
{
    /**
     * @var string
     */
    private $value;

    public function __construct(string $value)
    {
        if (\strlen($value) < 6) {
            throw new InvalidArgumentException('A password must be a string with at least 6 characters.');
        }

        $this->value = $value;
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

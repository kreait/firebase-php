<?php

declare(strict_types=1);

namespace Kreait\Firebase\Value;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Value;

/**
 * @internal
 */
class Uid implements Value, \JsonSerializable
{
    /**
     * @var string
     */
    private $value;

    /**
     * @internal
     */
    public function __construct(string $value)
    {
        if ($value === '' || \strlen($value) > 128) {
            throw new InvalidArgumentException('A uid must be a non-empty string with at most 128 characters.');
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

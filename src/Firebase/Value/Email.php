<?php

declare(strict_types=1);

namespace Kreait\Firebase\Value;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Value;

/**
 * @internal
 */
class Email implements Value, \JsonSerializable
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
        if (!\filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('The email address is invalid.');
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

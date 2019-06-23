<?php

declare(strict_types=1);

namespace Kreait\Firebase\Value;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Value;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

/**
 * @internal
 */
class PhoneNumber implements Value, \JsonSerializable
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
        $util = PhoneNumberUtil::getInstance();

        try {
            $parsed = $util->parse($value);
        } catch (NumberParseException $e) {
            throw new InvalidArgumentException('Invalid phone number: '.$e->getMessage());
        }

        $this->value = $util->format($parsed, PhoneNumberFormat::E164);
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

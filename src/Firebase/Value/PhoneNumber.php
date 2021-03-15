<?php

declare(strict_types=1);

namespace Kreait\Firebase\Value;

use Kreait\Firebase\Exception\InvalidArgumentException;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class PhoneNumber implements \JsonSerializable
{
    /** @var string */
    private $value;

    /**
     * @internal
     */
    public function __construct(string $value)
    {
        if (\class_exists(PhoneNumberUtil::class)) {
            $util = PhoneNumberUtil::getInstance();

            try {
                $parsed = $util->parse($value);
            } catch (NumberParseException $e) {
                throw new InvalidArgumentException('Invalid phone number: '.$e->getMessage());
            }

            $value = $util->format($parsed, PhoneNumberFormat::E164);
        }

        $this->value = $value;
    }

    public function __toString(): string
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

<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

class RegistrationToken implements \JsonSerializable
{
    /** @var string */
    private $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function fromValue(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString()
    {
        return $this->value;
    }

    public function jsonSerialize()
    {
        return $this->value;
    }
}

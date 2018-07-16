<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Kreait\Firebase\Exception\Messaging\InvalidArgument;

class Condition implements \JsonSerializable
{
    /**
     * @var string
     */
    private $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function fromValue(string $value): self
    {
        $value = str_replace('"', "'", $value);

        if ((substr_count($value, "'") % 2) !== 0) {
            throw new InvalidArgument(sprintf('The condition "%s" contains an uneven amount of quotes.', $value));
        }

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

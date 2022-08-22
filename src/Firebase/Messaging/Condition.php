<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use JsonSerializable;
use Kreait\Firebase\Exception\Messaging\InvalidArgument;

use function mb_strtolower;
use function mb_substr_count;
use function sprintf;
use function str_replace;

final class Condition implements JsonSerializable
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public function __toString()
    {
        return $this->value;
    }

    public static function fromValue(string $value): self
    {
        $value = str_replace('"', "'", $value);

        if ((mb_substr_count($value, "'") % 2) !== 0) {
            throw new InvalidArgument(sprintf('The condition "%s" contains an uneven amount of quotes.', $value));
        }

        if (mb_substr_count(mb_strtolower($value), 'in topics') > 5) {
            throw new InvalidArgument(sprintf('The condition "%s" containts more than 5 topics.', $value));
        }

        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}

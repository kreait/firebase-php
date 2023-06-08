<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use JsonSerializable;
use Kreait\Firebase\Exception\Messaging\InvalidArgument;
use Stringable;

use function mb_strtolower;
use function mb_substr_count;
use function sprintf;
use function str_replace;

final class Condition implements JsonSerializable, Stringable
{
    /**
     * @param non-empty-string $value
     */
    private function __construct(private readonly string $value)
    {
    }

    /**
     * @return non-empty-string
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * @param non-empty-string $value
     */
    public static function fromValue(string $value): self
    {
        $value = str_replace('"', "'", $value);

        if ((mb_substr_count($value, "'") % 2) !== 0) {
            throw new InvalidArgument(sprintf('The condition "%s" contains an uneven amount of quotes.', $value));
        }

        if (mb_substr_count(mb_strtolower($value), 'in topics') > 5) {
            throw new InvalidArgument(sprintf('The condition "%s" contains more than 5 topics.', $value));
        }

        return new self($value);
    }

    /**
     * @return non-empty-string
     */
    public function value(): string
    {
        return $this->value;
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}

<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use JsonSerializable;
use Kreait\Firebase\Exception\Messaging\InvalidArgument;
use Stringable;

use function preg_match;
use function preg_replace;
use function sprintf;
use function trim;

final class Topic implements JsonSerializable, Stringable
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
        $value = trim((string) preg_replace('@^/topic/@', '', $value), '/');

        if ($value === '') {
            throw new InvalidArgument('The topic name cannot be empty');
        }

        if (preg_match('/[^a-zA-Z0-9-_.~]$/', $value)) {
            throw new InvalidArgument(sprintf('Malformed topic name "%s".', $value));
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

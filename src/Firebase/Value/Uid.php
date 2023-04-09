<?php

declare(strict_types=1);

namespace Kreait\Firebase\Value;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Stringable;

use function mb_strlen;

/**
 * @internal
 */
final class Uid
{
    /**
     * @var non-empty-string
     */
    public readonly string $value;

    private function __construct(string $value)
    {
        if ($value === '' || mb_strlen($value) > 128) {
            throw new InvalidArgumentException('A uid must be a non-empty string with at most 128 characters.');
        }

        $this->value = $value;
    }

    public static function fromString(Stringable|string $value): self
    {
        return new self((string) $value);
    }
}

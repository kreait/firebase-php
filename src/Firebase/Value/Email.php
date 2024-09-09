<?php

declare(strict_types=1);

namespace Kreait\Firebase\Value;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Stringable;

use function filter_var;

use const FILTER_VALIDATE_EMAIL;

/**
 * @internal
 */
final class Email
{
    /**
     * @var non-empty-string
     */
    public readonly string $value;

    private function __construct(string $value)
    {
        if ($value === '' || !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('The email address is invalid.');
        }

        $this->value = $value;
    }

    public static function fromString(Stringable|string $value): self
    {
        return new self((string) $value);
    }
}

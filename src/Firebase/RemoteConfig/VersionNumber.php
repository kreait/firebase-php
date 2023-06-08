<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use JsonSerializable;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Stringable;

use function ctype_digit;

final class VersionNumber implements JsonSerializable, Stringable
{
    /**
     * @param non-empty-string $value
     */
    private function __construct(private readonly string $value)
    {
    }

    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * @param positive-int|non-empty-string $value
     */
    public static function fromValue($value): self
    {
        $valueString = (string) $value;

        if (!ctype_digit($valueString)) {
            throw new InvalidArgumentException('A version number should only consist of digits');
        }

        return new self($valueString);
    }

    /**
     * @return non-empty-string
     */
    public function jsonSerialize(): string
    {
        return $this->value;
    }

    /**
     * @param self|non-empty-string $other
     */
    public function equalsTo($other): bool
    {
        return $this->value === (string) $other;
    }
}

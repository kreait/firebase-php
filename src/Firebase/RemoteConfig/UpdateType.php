<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use JsonSerializable;
use Stringable;

final class UpdateType implements JsonSerializable, Stringable
{
    public const UNSPECIFIED = 'REMOTE_CONFIG_UPDATE_TYPE_UNSPECIFIED';
    public const INCREMENTAL_UPDATE = 'INCREMENTAL_UPDATE';
    public const FORCED_UPDATE = 'FORCED_UPDATE';
    public const ROLLBACK = 'ROLLBACK';

    private function __construct(private readonly string $value)
    {
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function fromValue(string $value): self
    {
        return new self($value);
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

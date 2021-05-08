<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

final class UpdateOrigin implements \JsonSerializable
{
    public const UNSPECIFIED = 'REMOTE_CONFIG_UPDATE_ORIGIN_UNSPECIFIED';
    public const CONSOLE = 'CONSOLE';
    public const REST_API = 'REST_API';

    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function fromValue(string $value): self
    {
        return new self($value);
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

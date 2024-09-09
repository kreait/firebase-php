<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use JsonSerializable;

/**
 * @deprecated 7.4.0
 *
 * @codeCoverageIgnore
 *
 * @phpstan-type RemoteConfigExplicitValueShape array{
 *     value: string
 * }
 */
final class ExplicitValue implements JsonSerializable
{
    /**
     * @param RemoteConfigExplicitValueShape $data
     */
    private function __construct(private readonly array $data)
    {
    }

    public static function fromString(string $value): self
    {
        return new self(['value' => $value]);
    }

    /**
     * @return RemoteConfigExplicitValueShape
     */
    public function toArray(): array
    {
        return $this->data;
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }
}

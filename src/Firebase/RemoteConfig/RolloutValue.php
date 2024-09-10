<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use JsonSerializable;

/**
 * @phpstan-type RemoteConfigRolloutValueShape array{
 *     rolloutId: non-empty-string,
 *     value: string,
 *     percent: int<0, 100>
 * }
 *
 * @see https://firebase.google.com/docs/reference/remote-config/rest/v1/RemoteConfig#rolloutvalue
 */
final class RolloutValue implements JsonSerializable
{
    /**
     * @param RemoteConfigRolloutValueShape $data
     */
    private function __construct(private readonly array $data)
    {
    }

    /**
     * @param RemoteConfigRolloutValueShape $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    /**
     * @return RemoteConfigRolloutValueShape
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * @return RemoteConfigRolloutValueShape
     */
    public function jsonSerialize(): array
    {
        return $this->data;
    }
}

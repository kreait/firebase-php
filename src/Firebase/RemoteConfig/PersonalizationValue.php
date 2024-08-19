<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use JsonSerializable;

/**
 * @phpstan-type RemoteConfigPersonalizationValueShape array{
 *    personalizationId: string
 * }
 */
final class PersonalizationValue implements JsonSerializable
{
    /**
     * @param RemoteConfigPersonalizationValueShape $data
     */
    public function __construct(private readonly array $data)
    {
    }

    /**
     * @param RemoteConfigPersonalizationValueShape $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    /**
     * @return RemoteConfigPersonalizationValueShape
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * @return RemoteConfigPersonalizationValueShape
     */
    public function jsonSerialize(): array
    {
        return $this->data;
    }
}

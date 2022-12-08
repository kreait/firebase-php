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
     * @var RemoteConfigPersonalizationValueShape
     */
    private array $data;

    /**
     * @param RemoteConfigPersonalizationValueShape $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @param RemoteConfigPersonalizationValueShape $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }
}

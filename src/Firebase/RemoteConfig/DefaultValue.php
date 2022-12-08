<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use JsonSerializable;

/**
 * @phpstan-import-type RemoteConfigPersonalizationValueShape from PersonalizationValue
 * @phpstan-import-type RemoteConfigExplicitValueShape from ExplicitValue
 *
 * @phpstan-type RemoteConfigInAppDefaultValueShape array{
 *     useInAppDefault: bool
 * }
 */
class DefaultValue implements JsonSerializable
{
    /**
     * @var RemoteConfigExplicitValueShape|RemoteConfigInAppDefaultValueShape|RemoteConfigPersonalizationValueShape
     */
    private array $data;

    /**
     * @param RemoteConfigExplicitValueShape|RemoteConfigInAppDefaultValueShape|RemoteConfigPersonalizationValueShape $data
     */
    private function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function useInAppDefault(): self
    {
        return new self(['useInAppDefault' => true]);
    }

    public static function with(string $value): self
    {
        return new self(['value' => $value]);
    }

    /**
     * @return RemoteConfigExplicitValueShape|RemoteConfigInAppDefaultValueShape|RemoteConfigPersonalizationValueShape
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * @param RemoteConfigExplicitValueShape|RemoteConfigInAppDefaultValueShape|RemoteConfigPersonalizationValueShape $data
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

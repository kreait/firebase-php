<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use JsonSerializable;

/**
 * @phpstan-import-type RemoteConfigParameterValueShape from ParameterValue
 *
 * @todo Deprecate/Remove in 8.0
 *
 * @see ParameterValue
 */
class DefaultValue implements JsonSerializable
{
    private function __construct(private readonly ParameterValue $value)
    {
    }

    public static function useInAppDefault(): self
    {
        return new self(ParameterValue::inAppDefault());
    }

    public static function with(string $value): self
    {
        return new self(ParameterValue::withValue($value));
    }

    /**
     * @param RemoteConfigParameterValueShape $data
     */
    public static function fromArray(array $data): self
    {
        return new self(ParameterValue::fromArray($data));
    }

    /**
     * @return RemoteConfigParameterValueShape
     */
    public function toArray(): array
    {
        return $this->value->toArray();
    }

    /**
     * @return RemoteConfigParameterValueShape
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}

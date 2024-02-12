<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use JsonSerializable;

use function array_key_exists;

/**
 * @phpstan-import-type RemoteConfigPersonalizationValueShape from PersonalizationValue
 *
 * @phpstan-type RemoteConfigParameterValueShape array{
 *     value?: string,
 *     useInAppDefault?: bool,
 *     personalizationValue?: RemoteConfigPersonalizationValueShape
 * }
 */
final class ParameterValue implements JsonSerializable
{
    private function __construct(
        private readonly ?string $value = null,
        private readonly ?bool $useInAppDefault = null,
        private readonly ?PersonalizationValue $personalizationValue = null,
    ) {
    }

    public static function withValue(string $value): self
    {
        return new self(value: $value);
    }

    public static function inAppDefault(): self
    {
        return new self(useInAppDefault: true);
    }

    public static function withPersonalizationValue(PersonalizationValue $value): self
    {
        return new self(personalizationValue: $value);
    }

    /**
     * @param RemoteConfigParameterValueShape $data
     */
    public static function fromArray(array $data): self
    {
        if (array_key_exists('value', $data)) {
            return self::withValue($data['value']);
        }

        if (array_key_exists('useInAppDefault', $data)) {
            return self::inAppDefault();
        }

        if (array_key_exists('personalizationValue', $data)) {
            return self::withPersonalizationValue(PersonalizationValue::fromArray($data['personalizationValue']));
        }

        return new self();
    }

    /**
     * @return RemoteConfigParameterValueShape
     */
    public function toArray(): array
    {
        if ($this->value !== null) {
            return ['value' => $this->value];
        }

        if ($this->useInAppDefault !== null) {
            return ['useInAppDefault' => $this->useInAppDefault];
        }

        if ($this->personalizationValue !== null) {
            return ['personalizationValue' => $this->personalizationValue->toArray()];
        }

        return [];
    }

    /**
     * @return RemoteConfigParameterValueShape
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}

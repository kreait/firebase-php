<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use JsonSerializable;

use function is_bool;
use function is_string;

/**
 * @phpstan-import-type RemoteConfigPersonalizationValueShape from PersonalizationValue
 * @phpstan-import-type RemoteConfigExplicitValueShape from ExplicitValue
 * @phpstan-import-type RemoteConfigInAppDefaultValueShape from DefaultValue
 *
 * @phpstan-type RemoteConfigParameterShape array{
 *     defaultValue?: RemoteConfigInAppDefaultValueShape|RemoteConfigExplicitValueShape|RemoteConfigPersonalizationValueShape,
 *     conditionalValues?: array<non-empty-string, RemoteConfigInAppDefaultValueShape|RemoteConfigExplicitValueShape|RemoteConfigPersonalizationValueShape>,
 *     description?: string
 * }
 */
class Parameter implements JsonSerializable
{
    /**
     * @var non-empty-string
     */
    private string $name;
    private ?string $description = '';
    private ?DefaultValue $defaultValue;

    /** @var list<ConditionalValue> */
    private array $conditionalValues = [];

    /**
     * @param non-empty-string $name
     */
    private function __construct(string $name, ?DefaultValue $defaultValue = null)
    {
        $this->name = $name;
        $this->defaultValue = $defaultValue;
    }

    /**
     * @param non-empty-string $name
     * @param DefaultValue|RemoteConfigInAppDefaultValueShape|RemoteConfigPersonalizationValueShape|RemoteConfigExplicitValueShape|string|bool|null $defaultValue
     */
    public static function named(string $name, $defaultValue = null): self
    {
        if ($defaultValue === null) {
            return new self($name, null);
        }

        if ($defaultValue instanceof DefaultValue) {
            return new self($name, $defaultValue);
        }

        if (is_string($defaultValue)) {
            return new self($name, DefaultValue::fromArray(['value' => $defaultValue]));
        }

        if (is_bool($defaultValue)) {
            return new self($name, DefaultValue::fromArray(['useInAppDefault' => $defaultValue]));
        }

        return new self($name, DefaultValue::fromArray($defaultValue));
    }

    /**
     * @return non-empty-string
     */
    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->description ?: '';
    }

    public function withDescription(string $description): self
    {
        $parameter = clone $this;
        $parameter->description = $description;

        return $parameter;
    }

    /**
     * @param DefaultValue|string $defaultValue
     */
    public function withDefaultValue($defaultValue): self
    {
        $defaultValue = $defaultValue instanceof DefaultValue ? $defaultValue : DefaultValue::with($defaultValue);

        $parameter = clone $this;
        $parameter->defaultValue = $defaultValue;

        return $parameter;
    }

    public function defaultValue(): ?DefaultValue
    {
        return $this->defaultValue;
    }

    public function withConditionalValue(ConditionalValue $conditionalValue): self
    {
        $parameter = clone $this;
        $parameter->conditionalValues[] = $conditionalValue;

        return $parameter;
    }

    /**
     * @return list<ConditionalValue>
     */
    public function conditionalValues(): array
    {
        return $this->conditionalValues;
    }

    /**
     * @return RemoteConfigParameterShape
     */
    public function toArray(): array
    {
        $conditionalValues = [];

        foreach ($this->conditionalValues() as $conditionalValue) {
            $conditionalValues[$conditionalValue->conditionName()] = $conditionalValue->toArray();
        }

        $array = [];

        if ($this->defaultValue !== null) {
            $array['defaultValue'] = $this->defaultValue->toArray();
        }

        if ($conditionalValues !== []) {
            $array['conditionalValues'] = $conditionalValues;
        }

        if ($this->description !== null && $this->description !== '') {
            $array['description'] = $this->description;
        }

        return $array;
    }

    /**
     * @return RemoteConfigParameterShape
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}

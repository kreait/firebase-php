<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use JsonSerializable;

use function is_bool;
use function is_string;

/**
 * @phpstan-import-type RemoteConfigParameterValueShape from ParameterValue
 *
 * @phpstan-type RemoteConfigParameterShape array{
 *     defaultValue?: RemoteConfigParameterValueShape,
 *     conditionalValues?: array<non-empty-string, RemoteConfigParameterValueShape>,
 *     description?: string
 * }
 */
class Parameter implements JsonSerializable
{
    private ?string $description = '';

    /**
     * @var list<ConditionalValue>
     */
    private array $conditionalValues = [];

    /**
     * @param non-empty-string $name
     */
    private function __construct(
        private readonly string $name,
        private ?ParameterValue $defaultValue = null,
    ) {
    }

    /**
     * @param non-empty-string $name
     * @param DefaultValue|RemoteConfigParameterValueShape|string|bool|null $defaultValue
     */
    public static function named(string $name, $defaultValue = null): self
    {
        if ($defaultValue === null) {
            return new self($name, null);
        }

        if ($defaultValue instanceof DefaultValue) {
            return new self($name, ParameterValue::fromArray($defaultValue->toArray()));
        }

        if (is_string($defaultValue)) {
            return new self($name, ParameterValue::withValue($defaultValue));
        }

        if (is_bool($defaultValue)) {
            return new self($name, ParameterValue::inAppDefault());
        }

        return new self($name, ParameterValue::fromArray($defaultValue));
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
     * @param DefaultValue|RemoteConfigParameterValueShape|string|bool|null $defaultValue
     */
    public function withDefaultValue($defaultValue): self
    {
        return self::named($this->name, $defaultValue);
    }

    public function defaultValue(): ?DefaultValue
    {
        if ($this->defaultValue === null) {
            return null;
        }

        return DefaultValue::fromArray($this->defaultValue->toArray());
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

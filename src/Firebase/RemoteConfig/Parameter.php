<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use Kreait\Firebase\Exception\InvalidArgumentException;

class Parameter implements \JsonSerializable
{
    private string $name;
    private string $description = '';
    private DefaultValue $defaultValue;
    /** @var ConditionalValue[] */
    private array $conditionalValues = [];

    private function __construct(string $name, DefaultValue $defaultValue)
    {
        $this->name = $name;
        $this->defaultValue = $defaultValue;
    }

    /**
     * @param DefaultValue|string|mixed $defaultValue
     */
    public static function named(string $name, $defaultValue = null): self
    {
        if ($defaultValue === null) {
            $defaultValue = DefaultValue::none();
        } elseif (\is_string($defaultValue)) {
            $defaultValue = DefaultValue::with($defaultValue);
        } else {
            throw new InvalidArgumentException('The default value for a remote config parameter must be a string or NULL to use the in-app default.');
        }

        return new self($name, $defaultValue);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->description;
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

    public function defaultValue(): DefaultValue
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
     * @return ConditionalValue[]
     */
    public function conditionalValues(): array
    {
        return $this->conditionalValues;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $conditionalValues = [];
        foreach ($this->conditionalValues() as $conditionalValue) {
            $conditionalValues[$conditionalValue->conditionName()] = $conditionalValue->jsonSerialize();
        }

        return \array_filter([
            'defaultValue' => $this->defaultValue,
            'conditionalValues' => $conditionalValues,
            'description' => $this->description,
        ]);
    }
}

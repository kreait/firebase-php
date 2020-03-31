<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use Kreait\Firebase\Exception\InvalidArgumentException;

class Parameter implements \JsonSerializable
{
    /** @var string */
    private $name;

    /** @var string */
    private $description = '';

    /** @var DefaultValue */
    private $defaultValue;

    /** @var ConditionalValue[] */
    private $conditionalValues = [];

    private function __construct()
    {
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

        $parameter = new self();
        $parameter->name = $name;
        $parameter->defaultValue = $defaultValue;

        return $parameter;
    }

    public function name(): string
    {
        return $this->name;
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
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        \reset($data);
        $parameterData = \current($data);

        $parameter = new self();
        $parameter->name = (string) \key($data);
        $parameter->defaultValue = DefaultValue::fromArray($parameterData['defaultValue'] ?? []);

        foreach ((array) ($parameterData['conditionalValues'] ?? []) as $key => $conditionalValueData) {
            $parameter = $parameter->withConditionalValue(new ConditionalValue($key, $conditionalValueData['value']));
        }

        if (\is_string($parameterData['description'] ?? null)) {
            $parameter->description = $parameterData['description'];
        }

        return $parameter;
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

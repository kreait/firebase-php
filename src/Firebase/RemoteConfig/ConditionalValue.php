<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

class ConditionalValue implements \JsonSerializable
{
    /**
     * @var string
     */
    private $conditionName;

    /**
     * @var string
     */
    private $value;

    /**
     * @internal
     */
    public function __construct(string $conditionName, string $value)
    {
        $this->conditionName = $conditionName;
        $this->value = $value;
    }

    public function conditionName(): string
    {
        return $this->conditionName;
    }

    /**
     * @param string|Condition $condition
     *
     * @return self
     */
    public static function basedOn($condition): self
    {
        $name = $condition instanceof Condition ? $condition->name() : $condition;

        return new self($name, '');
    }

    public function value(): string
    {
        return $this->value;
    }

    public function withValue(string $value): self
    {
        $conditionalValue = clone $this;
        $conditionalValue->value = $value;

        return $conditionalValue;
    }

    public function jsonSerialize()
    {
        return ['value' => $this->value];
    }
}

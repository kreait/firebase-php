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

    public function __construct(string $conditionName, string $value)
    {
        $this->conditionName = $conditionName;
        $this->value = $value;
    }

    public function conditionName(): string
    {
        return $this->conditionName;
    }

    public static function basedOn(Condition $condition): self
    {
        return new self($condition->name(), '');
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

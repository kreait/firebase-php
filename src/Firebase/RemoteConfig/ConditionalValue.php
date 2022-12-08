<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use JsonSerializable;

use function is_string;

/**
 * @phpstan-import-type RemoteConfigPersonalizationValueShape from PersonalizationValue
 * @phpstan-import-type RemoteConfigExplicitValueShape from ExplicitValue
 * @phpstan-import-type RemoteConfigInAppDefaultValueShape from DefaultValue
 */
class ConditionalValue implements JsonSerializable
{
    /**
     * @var non-empty-string
     */
    private string $conditionName;

    /**
     * @var RemoteConfigExplicitValueShape|RemoteConfigInAppDefaultValueShape|RemoteConfigPersonalizationValueShape|string
     */
    private string|array $data;

    /**
     * @internal
     *
     * @param non-empty-string $conditionName
     * @param RemoteConfigExplicitValueShape|RemoteConfigInAppDefaultValueShape|RemoteConfigPersonalizationValueShape|string $data
     */
    public function __construct(string $conditionName, array|string $data)
    {
        $this->conditionName = $conditionName;
        $this->data = $data;
    }

    /**
     * @return non-empty-string
     */
    public function conditionName(): string
    {
        return $this->conditionName;
    }

    /**
     * @param non-empty-string|Condition $condition
     */
    public static function basedOn($condition): self
    {
        $name = $condition instanceof Condition ? $condition->name() : $condition;

        return new self($name, ['value' => '']);
    }

    /**
     * @return RemoteConfigExplicitValueShape|RemoteConfigInAppDefaultValueShape|RemoteConfigPersonalizationValueShape|string
     */
    public function value()
    {
        return $this->data;
    }

    /**
     * @param RemoteConfigExplicitValueShape|RemoteConfigInAppDefaultValueShape|RemoteConfigPersonalizationValueShape|string $value
     */
    public function withValue($value): self
    {
        return new self($this->conditionName, $value);
    }

    /**
     * @return RemoteConfigExplicitValueShape|RemoteConfigInAppDefaultValueShape|RemoteConfigPersonalizationValueShape
     */
    public function toArray(): array
    {
        if (is_string($this->data)) {
            return ExplicitValue::fromString($this->data)->toArray();
        }

        return $this->data;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}

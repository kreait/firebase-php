<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use JsonSerializable;
use Kreait\Firebase\Exception\InvalidArgumentException;

use function array_filter;
use function array_key_exists;
use function array_map;
use function array_unique;
use function array_values;
use function in_array;
use function sprintf;

/**
 * @phpstan-import-type RemoteConfigConditionShape from Condition
 * @phpstan-import-type RemoteConfigParameterShape from Parameter
 * @phpstan-import-type RemoteConfigParameterGroupShape from ParameterGroup
 * @phpstan-import-type RemoteConfigVersionShape from Version
 *
 * @phpstan-type RemoteConfigTemplateShape array{
 *     conditions?: list<RemoteConfigConditionShape>,
 *     parameters?: array<non-empty-string, RemoteConfigParameterShape>,
 *     version?: RemoteConfigVersionShape,
 *     parameterGroups?: array<non-empty-string, RemoteConfigParameterGroupShape>
 * }
 */
class Template implements JsonSerializable
{
    private string $etag = '*';

    /** @var array<non-empty-string, Parameter> */
    private array $parameters = [];

    /** @var array<non-empty-string, ParameterGroup> */
    private array $parameterGroups = [];

    /** @var list<Condition> */
    private array $conditions = [];
    private ?Version $version = null;

    private function __construct()
    {
    }

    public static function new(): self
    {
        return new self();
    }

    /**
     * @param RemoteConfigTemplateShape $data
     */
    public static function fromArray(array $data, ?string $etag = null): self
    {
        $template = new self();
        $template->etag = $etag ?? '*';

        foreach (($data['conditions'] ?? []) as $conditionData) {
            $template = $template->withCondition(self::buildCondition($conditionData['name'], $conditionData));
        }

        foreach (($data['parameters'] ?? []) as $name => $parameterData) {
            $template = $template->withParameter(self::buildParameter($name, $parameterData));
        }

        foreach (($data['parameterGroups'] ?? []) as $name => $parameterGroupData) {
            $template = $template->withParameterGroup(self::buildParameterGroup($name, $parameterGroupData));
        }

        $versionData = $data['version'] ?? null;

        if ($versionData !== null) {
            $template->version = Version::fromArray($versionData);
        }

        return $template;
    }

    /**
     * @internal
     */
    public function etag(): string
    {
        return $this->etag;
    }

    /**
     * @return Condition[]
     */
    public function conditions(): array
    {
        return $this->conditions;
    }

    /**
     * @return array<non-empty-string, Parameter>
     */
    public function parameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return ParameterGroup[]
     */
    public function parameterGroups(): array
    {
        return $this->parameterGroups;
    }

    public function version(): ?Version
    {
        return $this->version;
    }

    public function withParameter(Parameter $parameter): self
    {
        $this->assertThatAllConditionalValuesAreValid($parameter);

        $template = clone $this;
        $template->parameters[$parameter->name()] = $parameter;

        return $template;
    }

    /**
     * @param non-empty-string $name
     */
    public function withRemovedParameter(string $name): self
    {
        $parameters = $this->parameters;
        unset($parameters[$name]);

        $template = clone $this;
        $template->parameters = $parameters;

        return $template;
    }

    public function withParameterGroup(ParameterGroup $parameterGroup): self
    {
        $template = clone $this;
        $template->parameterGroups[$parameterGroup->name()] = $parameterGroup;

        return $template;
    }

    public function withRemovedParameterGroup(string $name): self
    {
        $groups = $this->parameterGroups;
        unset($groups[$name]);

        $template = clone $this;
        $template->parameterGroups = $groups;

        return $template;
    }

    public function withCondition(Condition $condition): self
    {
        $template = clone $this;
        $template->conditions[] = $condition;

        return $template;
    }

    /**
     * @return list<non-empty-string>
     */
    public function conditionNames(): array
    {
        return array_values(array_unique(
            array_map(static fn (Condition $c) => $c->name(), $this->conditions),
        ));
    }

    /**
     * @param non-empty-string $name
     */
    public function withRemovedCondition(string $name): self
    {
        $template = clone $this;
        $template->conditions = array_values(
            array_filter($this->conditions, static fn (Condition $c) => $c->name() !== $name),
        );

        return $template;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'conditions' => empty($this->conditions) ? null : array_values($this->conditions),
            'parameters' => empty($this->parameters) ? null : $this->parameters,
            'parameterGroups' => empty($this->parameterGroups) ? null : $this->parameterGroups,
        ];
    }

    /**
     * @param non-empty-string $name
     * @param RemoteConfigConditionShape $data
     */
    private static function buildCondition(string $name, array $data): Condition
    {
        $condition = Condition::named($name)->withExpression($data['expression']);

        if ($tagColor = $data['tagColor'] ?? null) {
            return $condition->withTagColor(new TagColor($tagColor));
        }

        return $condition;
    }

    /**
     * @param non-empty-string $name
     * @param RemoteConfigParameterShape $data
     */
    private static function buildParameter(string $name, array $data): Parameter
    {
        $parameter = Parameter::named($name)->withDescription((string) ($data['description'] ?? ''));

        if (array_key_exists('defaultValue', $data) && $data['defaultValue'] !== null) {
            $parameter = $parameter->withDefaultValue(DefaultValue::fromArray($data['defaultValue']));
        }

        foreach ((array) ($data['conditionalValues'] ?? []) as $key => $conditionalValueData) {
            $parameter = $parameter->withConditionalValue(new ConditionalValue($key, $conditionalValueData));
        }

        return $parameter;
    }

    /**
     * @param non-empty-string $name
     * @param RemoteConfigParameterGroupShape $parameterGroupData
     */
    private static function buildParameterGroup(string $name, array $parameterGroupData): ParameterGroup
    {
        $group = ParameterGroup::named($name)
            ->withDescription((string) ($parameterGroupData['description'] ?? ''));

        foreach ($parameterGroupData['parameters'] as $parameterName => $parameterData) {
            $group = $group->withParameter(self::buildParameter($parameterName, $parameterData));
        }

        return $group;
    }

    private function assertThatAllConditionalValuesAreValid(Parameter $parameter): void
    {
        $conditionNames = array_map(static fn (Condition $c) => $c->name(), $this->conditions);

        foreach ($parameter->conditionalValues() as $conditionalValue) {
            if (!in_array($conditionalValue->conditionName(), $conditionNames, true)) {
                $message = 'The conditional value of the parameter named "%s" refers to a condition "%s" which does not exist.';

                throw new InvalidArgumentException(sprintf($message, $parameter->name(), $conditionalValue->conditionName()));
            }
        }
    }
}

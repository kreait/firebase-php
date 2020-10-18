<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Util\JSON;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class Template implements \JsonSerializable
{
    /** @var string */
    private $etag = '*';

    /** @var Parameter[] */
    private $parameters = [];

    /** @var ParameterGroup[] */
    private $parameterGroups = [];

    /** @var Condition[] */
    private $conditions = [];

    /** @var Version|null */
    private $version;

    private function __construct()
    {
    }

    public static function new(): self
    {
        return new self();
    }

    /**
     * @internal
     */
    public static function fromResponse(ResponseInterface $response): self
    {
        $etagHeader = $response->getHeader('ETag');
        $etag = \array_shift($etagHeader) ?: '*';
        $data = JSON::decode((string) $response->getBody(), true);

        return self::fromArray($data, $etag);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data, ?string $etag = null): self
    {
        $template = new self();
        $template->etag = $etag ?? '*';

        foreach ((array) ($data['conditions'] ?? []) as $conditionData) {
            $template->conditions[(string) $conditionData['name']] = Condition::fromArray($conditionData);
        }

        foreach ((array) ($data['parameters'] ?? []) as $name => $parameterData) {
            $template->parameters[(string) $name] = Parameter::fromArray([(string) $name => $parameterData]);
        }

        foreach ((array) ($data['parameterGroups'] ?? []) as $name => $parameterGroupData) {
            $group = ParameterGroup::named((string) $name)
                ->withDescription((string) ($parameterGroupData['description'] ?? ''));

            foreach ($parameterGroupData['parameters'] ?? [] as $parameterName => $parameterData) {
                $parameter = Parameter::named($parameterName)
                    ->withDescription((string) ($parameterData['description'] ?? ''))
                    ->withDefaultValue(DefaultValue::fromArray($parameterData['defaultValue'] ?? []));

                foreach ((array) ($parameterData['conditionalValues'] ?? []) as $key => $conditionalValueData) {
                    $parameter = $parameter->withConditionalValue(new ConditionalValue($key, $conditionalValueData['value']));
                }

                $group = $group->withParameter($parameter);
            }

            $template->parameterGroups[$group->name()] = $group;
        }

        if (\is_array($data['version'] ?? null)) {
            try {
                $template->version = Version::fromArray($data['version']);
            } catch (Throwable $e) {
                $template->version = null;
            }
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
     * @return Parameter[]
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

    public function withParameter(Parameter $parameter): Template
    {
        $this->assertThatAllConditionalValuesAreValid($parameter);

        $template = clone $this;
        $template->parameters[$parameter->name()] = $parameter;

        return $template;
    }

    public function withParameterGroup(ParameterGroup $parameterGroup): Template
    {
        $template = clone $this;
        $template->parameterGroups[$parameterGroup->name()] = $parameterGroup;

        return $template;
    }

    public function withCondition(Condition $condition): Template
    {
        $template = clone $this;
        $template->conditions[$condition->name()] = $condition;

        return $template;
    }

    private function assertThatAllConditionalValuesAreValid(Parameter $parameter): void
    {
        foreach ($parameter->conditionalValues() as $conditionalValue) {
            if (!\array_key_exists($conditionalValue->conditionName(), $this->conditions)) {
                $message = 'The conditional value of the parameter named "%s" refers to a condition "%s" which does not exist.';

                throw new InvalidArgumentException(\sprintf($message, $parameter->name(), $conditionalValue->conditionName()));
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'conditions' => \array_values($this->conditions),
            'parameters' => !empty($this->parameters) ? $this->parameters : null,
            'parameterGroups' => !empty($this->parameterGroups) ? $this->parameterGroups : null,
        ];
    }
}

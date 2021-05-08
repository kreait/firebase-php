<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

final class ParameterGroup implements \JsonSerializable
{
    private string $name;

    private string $description = '';

    /** @var Parameter[] */
    private array $parameters = [];

    private function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function named(string $name): self
    {
        return new self($name);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->description;
    }

    /**
     * @return Parameter[]
     */
    public function parameters(): array
    {
        return $this->parameters;
    }

    public function withDescription(string $description): self
    {
        $group = clone $this;
        $group->description = $description;

        return $group;
    }

    public function withParameter(Parameter $parameter): self
    {
        $group = clone $this;
        $group->parameters[$parameter->name()] = $parameter;

        return $group;
    }

    public function jsonSerialize()
    {
        return [
            'description' => $this->description,
            'parameters' => $this->parameters,
        ];
    }
}

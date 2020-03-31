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

    /** @var Condition[] */
    private $conditions = [];

    /** @var Version|null */
    private $version;

    private function __construct()
    {
    }

    public static function new(): self
    {
        $template = new self();
        $template->etag = '*';
        $template->parameters = [];

        return $template;
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
                $message = 'The conditional value of the parameter named "%s" referes to a condition "%s" which does not exist.';

                throw new InvalidArgumentException(\sprintf($message, $parameter->name(), $conditionalValue->conditionName()));
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $result = [
            'conditions' => \array_values($this->conditions),
            'parameters' => $this->parameters,
        ];

        return \array_filter($result);
    }
}

<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Util\JSON;
use Psr\Http\Message\ResponseInterface;

class Template implements \JsonSerializable
{
    /**
     * @var string
     */
    private $etag = '*';

    /**
     * @var Parameter[]
     */
    private $parameters = [];

    /**
     * @var Condition[]
     */
    private $conditions = [];

    public static function new(): self
    {
        $template = new self();
        $template->etag = '*';
        $template->parameters = [];

        return $template;
    }

    public static function fromResponse(ResponseInterface $response): self
    {
        $etagHeader = $response->getHeader('ETag');
        $etag = array_shift($etagHeader) ?? '*';
        $data = JSON::decode((string) $response->getBody(), true);

        return self::fromArray($data, $etag);
    }

    public static function fromArray(array $data, string $etag = '*'): self
    {
        $template = new self();
        $template->etag = $etag;

        foreach ((array) ($data['conditions'] ?? []) as $conditionData) {
            $template->conditions[] = Condition::fromArray($conditionData);
        }

        foreach ((array) ($data['parameters'] ?? []) as $name => $parameterData) {
            $template->parameters[$name] = Parameter::fromArray([$name => $parameterData]);
        }

        return $template;
    }

    public function getEtag(): string
    {
        return $this->etag;
    }

    public function withParameter(Parameter $parameter)
    {
        $this->assertThatAllConditionalValuesAreValid($parameter);

        $template = clone $this;
        $template->parameters[$parameter->name()] = $parameter;

        return $template;
    }

    public function withCondition(Condition $condition)
    {
        $template = clone $this;
        $template->conditions[$condition->name()] = $condition;

        return $template;
    }

    private function assertThatAllConditionalValuesAreValid(Parameter $parameter)
    {
        $allValid = array_reduce($parameter->conditionalValues(), function (bool $result, ConditionalValue $conditionalValue) {
            return $result ?: $this->conditionExists($conditionalValue->conditionName());
        }, false);

        if (!$allValid && \count($parameter->conditionalValues())) {
            throw new InvalidArgumentException('Not all given conditional values refer to an existing condition.');
        }
    }

    private function conditionExists(string $conditionName): bool
    {
        return array_key_exists($conditionName, $this->conditions);
    }

    public function jsonSerialize()
    {
        $result = [
            'conditions' => array_values($this->conditions),
            'parameters' => $this->parameters,
        ];

        return array_filter($result);
    }
}

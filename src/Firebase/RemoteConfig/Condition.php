<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use JsonSerializable;

/**
 * @phpstan-type RemoteConfigConditionShape array{
 *     name: non-empty-string,
 *     expression: non-empty-string,
 *     tagColor?: ?non-empty-string
 * }
 */
class Condition implements JsonSerializable
{
    /**
     * @var non-empty-string
     */
    private string $name;

    /**
     * @var non-empty-string
     */
    private string $expression;
    private ?TagColor $tagColor;

    /**
     * @param non-empty-string $name
     * @param non-empty-string $expression
     */
    private function __construct(string $name, string $expression, ?TagColor $tagColor = null)
    {
        $this->name = $name;
        $this->expression = $expression;
        $this->tagColor = $tagColor;
    }

    /**
     * @param RemoteConfigConditionShape $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'],
            $data['expression'],
            isset($data['tagColor']) ? new TagColor($data['tagColor']) : null,
        );
    }

    /**
     * @param non-empty-string $name
     */
    public static function named(string $name): self
    {
        return new self($name, 'false', null);
    }

    /**
     * @return non-empty-string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return non-empty-string
     */
    public function expression(): string
    {
        return $this->expression;
    }

    /**
     * @param non-empty-string $expression
     */
    public function withExpression(string $expression): self
    {
        $condition = clone $this;
        $condition->expression = $expression;

        return $condition;
    }

    /**
     * @param TagColor|non-empty-string $tagColor
     */
    public function withTagColor($tagColor): self
    {
        $tagColor = $tagColor instanceof TagColor ? $tagColor : new TagColor($tagColor);

        $condition = clone $this;
        $condition->tagColor = $tagColor;

        return $condition;
    }

    /**
     * @return RemoteConfigConditionShape
     */
    public function toArray(): array
    {
        $array = [
            'name' => $this->name,
            'expression' => $this->expression,
        ];

        if ($this->tagColor !== null) {
            $array['tagColor'] = $this->tagColor->value();
        }

        return $array;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}

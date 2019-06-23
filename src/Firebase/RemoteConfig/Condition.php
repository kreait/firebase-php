<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

class Condition implements \JsonSerializable
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $expression;

    /**
     * @var TagColor|null
     */
    private $tagColor;

    private function __construct(string $name, string $expression, TagColor $tagColor = null)
    {
        $this->name = $name;
        $this->expression = $expression;
        $this->tagColor = $tagColor;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'],
            $data['expression'],
            isset($data['tagColor']) ? new TagColor($data['tagColor']) : null
        );
    }

    public static function named(string $name): self
    {
        return new self($name, 'false', null);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function withExpression(string $expression): self
    {
        $condition = clone $this;
        $condition->expression = $expression;

        return $condition;
    }

    public function withTagColor($tagColor): self
    {
        $tagColor = $tagColor instanceof TagColor ? $tagColor : new TagColor($tagColor);

        $condition = clone $this;
        $condition->tagColor = $tagColor;

        return $condition;
    }

    public function jsonSerialize()
    {
        return \array_filter([
            'name' => $this->name,
            'expression' => $this->expression,
            'tagColor' => $this->tagColor ? $this->tagColor->value() : null,
        ], static function ($value) {
            return $value !== null;
        });
    }
}

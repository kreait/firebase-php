<?php

declare(strict_types=1);

namespace Kreait\Firebase\Request;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Request;

final class CreateUser implements Request
{
    /** @phpstan-use EditUserTrait<self> */
    use EditUserTrait;

    private function __construct()
    {
    }

    public static function new(): self
    {
        return new self();
    }

    /**
     * @param array<string, mixed> $properties
     *
     * @throws InvalidArgumentException when invalid properties have been provided
     */
    public static function withProperties(array $properties): self
    {
        return self::withEditableProperties(new self(), $properties);
    }

    public function jsonSerialize(): array
    {
        return $this->prepareJsonSerialize();
    }
}

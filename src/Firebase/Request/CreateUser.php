<?php

declare(strict_types=1);

namespace Kreait\Firebase\Request;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Request;

final class CreateUser implements Request
{
    use EditUserTrait;

    private function __construct()
    {
    }

    public static function new(): self
    {
        return new self();
    }

    /**
     * @throws InvalidArgumentException when invalid properties have been provided
     */
    public static function withProperties(array $properties): self
    {
        return self::withEditableProperties(new self(), $properties);
    }

    public function jsonSerialize()
    {
        return $this->prepareJsonSerialize();
    }
}

<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;

class AndroidConfig implements Config
{
    use ConfigTrait;

    private function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function fromArray(array $data): self
    {

        try {
            return new self($data);
        } catch (\Throwable $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function jsonSerialize()
    {
        return array_filter($this->data, function ($value) {
            return null !== $value;
        });
    }
}

<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;

final class MessageData implements \JsonSerializable
{
    /**
     * @var array
     */
    private $data = [];

    private function __construct()
    {
    }

    public static function empty(): self
    {
        return new self();
    }

    public static function fromArray(array $data): self
    {
        foreach ($data as $key => $value) {
            if (!\is_string($key) || !\is_string($value)) {
                throw new InvalidArgumentException('The keys and values in message data must be all strings.');
            }
        }

        $new = new self();
        $new->data = $data;

        return $new;
    }

    public function data(): array
    {
        return $this->data;
    }

    public function isEmpty(): bool
    {
        return empty($this->data);
    }

    public function jsonSerialize()
    {
        return $this->data;
    }
}

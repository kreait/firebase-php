<?php

declare(strict_types=1);

namespace Kreait\Firebase\Value;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Value;
use Psr\Http\Message\UriInterface;
use function GuzzleHttp\Psr7\uri_for;

class Url implements Value, \JsonSerializable
{
    /**
     * @var UriInterface
     */
    private $value;

    public function __construct(UriInterface $value)
    {
        $this->value = $value;
    }

    public static function fromValue($value): self
    {
        try {
            return new self(uri_for($value));
        } catch (\Throwable $e) {
            throw new InvalidArgumentException($e->getMessage());
        }
    }

    public function toUri(): UriInterface
    {
        return $this->value;
    }

    public function __toString()
    {
        return (string) $this->value;
    }

    public function jsonSerialize()
    {
        return (string) $this->value;
    }

    public function equalsTo($other): bool
    {
        return (string) $this->value === (string) $other;
    }
}

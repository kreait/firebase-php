<?php

declare(strict_types=1);

namespace Kreait\Firebase\Value;

use GuzzleHttp\Psr7\Uri;
use JsonSerializable;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Psr\Http\Message\UriInterface;
use Stringable;
use Throwable;

/**
 * @internal
 */
final class Url implements JsonSerializable
{
    private UriInterface $value;

    public function __construct(UriInterface $value)
    {
        $this->value = $value;
    }

    public function __toString()
    {
        return (string) $this->value;
    }

    /**
     * @param Stringable|string $value
     *
     * @throws InvalidArgumentException
     */
    public static function fromValue($value): self
    {
        if ($value instanceof UriInterface) {
            return new self($value);
        }

        try {
            return new self(new Uri((string) $value));
        } catch (Throwable $e) {
            throw new InvalidArgumentException($e->getMessage());
        }
    }

    public function toUri(): UriInterface
    {
        return $this->value;
    }

    public function jsonSerialize(): string
    {
        return (string) $this->value;
    }

    /**
     * @param Stringable|string $other
     */
    public function equalsTo($other): bool
    {
        return (string) $this->value === (string) $other;
    }
}

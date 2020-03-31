<?php

declare(strict_types=1);

namespace Kreait\Firebase\Value;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Psr\Http\Message\UriInterface;
use Throwable;

class Url implements \JsonSerializable
{
    /** @var UriInterface */
    private $value;

    /**
     * @internal
     */
    public function __construct(UriInterface $value)
    {
        $this->value = $value;
    }

    /**
     * @param string|Url|UriInterface|mixed $value
     *
     * @throws InvalidArgumentException
     */
    public static function fromValue($value): self
    {
        if ($value instanceof UriInterface) {
            return new self($value);
        }

        if ($value instanceof self) {
            return new self($value->toUri());
        }

        if (\is_string($value)) {
            try {
                return new self(new Uri($value));
            } catch (Throwable $e) {
                throw new InvalidArgumentException($e->getMessage());
            }
        }

        throw new InvalidArgumentException('Unable to parse given value to an URL');
    }

    public function toUri(): UriInterface
    {
        return $this->value;
    }

    public function __toString()
    {
        return (string) $this->value;
    }

    public function jsonSerialize(): string
    {
        return (string) $this->value;
    }

    /**
     * @param self|string $other
     */
    public function equalsTo($other): bool
    {
        return (string) $this->value === (string) $other;
    }
}

<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Countable;
use IteratorAggregate;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Traversable;

/**
 * @implements IteratorAggregate<RegistrationToken>
 */
final class RegistrationTokens implements Countable, IteratorAggregate
{
    /** @var RegistrationToken[] */
    private array $tokens;

    public function __construct(RegistrationToken ...$tokens)
    {
        $this->tokens = $tokens;
    }

    /**
     * @param RegistrationTokens|RegistrationToken|RegistrationToken[]|string[]|string $values
     *
     * @throws InvalidArgumentException
     */
    public static function fromValue($values): self
    {
        if ($values instanceof self) {
            $tokens = $values->values();
        } elseif ($values instanceof RegistrationToken) {
            $tokens = [$values];
        } elseif (\is_string($values)) {
            $tokens = [RegistrationToken::fromValue($values)];
        } elseif (\is_array($values)) {
            $tokens = [];

            foreach ($values as $value) {
                if ($value instanceof RegistrationToken) {
                    $tokens[] = $value;
                } elseif (\is_string($value)) {
                    $tokens[] = RegistrationToken::fromValue($value);
                }
            }
        } else {
            throw new InvalidArgumentException('Unsupported value(s)');
        }

        return new self(...$tokens);
    }

    /**
     * @codeCoverageIgnore
     *
     * @return Traversable<RegistrationToken>|RegistrationToken[]
     */
    public function getIterator(): iterable
    {
        yield from $this->tokens;
    }

    public function isEmpty(): bool
    {
        return \count($this->tokens) === 0;
    }

    /**
     * @return array<RegistrationToken>
     */
    public function values(): array
    {
        return $this->tokens;
    }

    /**
     * @return string[]
     */
    public function asStrings(): array
    {
        return \array_map('strval', $this->tokens);
    }

    public function count(): int
    {
        return \count($this->tokens);
    }

    /**
     * @param RegistrationToken|string $token
     */
    public function has($token): bool
    {
        $token = $token instanceof RegistrationToken ? $token : RegistrationToken::fromValue($token);

        foreach ($this->tokens as $existing) {
            if ($existing->value() === $token->value()) {
                return true;
            }
        }

        return false;
    }
}

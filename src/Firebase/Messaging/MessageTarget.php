<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;

use function mb_strtolower;

final class MessageTarget
{
    public const CONDITION = 'condition';
    public const TOKEN = 'token';
    public const TOPIC = 'topic';

    /**
     * @internal
     */
    public const UNKNOWN = 'unknown';
    public const TYPES = [
        self::CONDITION, self::TOKEN, self::TOPIC, self::UNKNOWN,
    ];

    /**
     * @param non-empty-string $type
     * @param non-empty-string $value
     */
    private function __construct(
        private readonly string $type,
        private readonly string $value,
    ) {
    }

    /**
     * Create a new message target with the given type and value.
     *
     * @param self::CONDITION|self::TOKEN|self::TOPIC|self::UNKNOWN $type
     * @param non-empty-string $value
     *
     * @throws InvalidArgumentException
     */
    public static function with(string $type, string $value): self
    {
        $targetType = mb_strtolower($type);

        $targetValue = match ($targetType) {
            self::CONDITION => Condition::fromValue($value)->value(),
            self::TOKEN => RegistrationToken::fromValue($value)->value(),
            self::TOPIC => Topic::fromValue($value)->value(),
            self::UNKNOWN => $value,
        };

        return new self($targetType, $targetValue);
    }

    /**
     * @return non-empty-string
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * @return non-empty-string
     */
    public function value(): string
    {
        return $this->value;
    }
}

<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;

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

    /** @var string */
    private $type;

    /** @var string */
    private $value;

    private function __construct()
    {
    }

    /**
     * Create a new message target with the given type and value.
     *
     * @throws InvalidArgumentException
     *
     * @return MessageTarget
     */
    public static function with(string $type, string $value): self
    {
        $targetType = \mb_strtolower($type);

        $new = new self();
        $new->type = $targetType;

        switch ($targetType) {
            case self::CONDITION:
                $new->value = (string) Condition::fromValue($value);
                break;
            case self::TOKEN:
                $new->value = (string) RegistrationToken::fromValue($value);
                break;
            case self::TOPIC:
                $new->value = (string) Topic::fromValue($value);
                break;
            case self::UNKNOWN:
                $new->value = $value;
                break;
            default:
                throw new InvalidArgumentException("Invalid target type '{$type}', valid types: ".\implode(', ', self::TYPES));
        }

        return $new;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function value(): string
    {
        return $this->value;
    }
}

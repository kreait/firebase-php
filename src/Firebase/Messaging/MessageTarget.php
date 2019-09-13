<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;

final class MessageTarget
{
    const CONDITION = 'condition';
    const TOKEN = 'token';
    const TOPIC = 'topic';

    const TYPES = [
        self::CONDITION, self::TOKEN, self::TOPIC,
    ];

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
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

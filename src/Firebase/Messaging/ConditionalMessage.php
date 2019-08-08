<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;

/**
 * @deprecated 4.14 Use CloudMessage instead
 */
class ConditionalMessage extends CloudMessage
{
    /** @var Condition */
    private $condition;

    /**
     * @deprecated 4.14 Use CloudMessage::withTarget('condition', $condition) instead
     * @see CloudMessage::withTarget()
     *
     * @param Condition|string $condition
     *
     * @return ConditionalMessage
     */
    public static function create($condition): self
    {
        $condition = $condition instanceof Condition ? $condition : Condition::fromValue($condition);

        $message = static::withTarget('condition', $condition->value());
        $message->condition = $condition;

        return $message;
    }

    /**
     * @deprecated 4.14 Use CloudMessage::fromArray() instead
     * @see CloudMessage::fromArray()
     *
     * @throws InvalidArgumentException
     *
     * @return static
     */
    public static function fromArray(array $data)
    {
        if (!($condition = $data['condition'] ?? null)) {
            throw new InvalidArgumentException('Missing field "condition"');
        }

        $condition = $condition instanceof Condition ? $condition : Condition::fromValue((string) $condition);

        $message = parent::fromArray($data);
        $message->condition = $condition;

        return $message;
    }

    /**
     * @deprecated 4.29.0 Use CloudMessage instead
     */
    public function condition(): string
    {
        return (string) $this->condition;
    }
}

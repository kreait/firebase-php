<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;

/**
 * @deprecated 4.14 Use CloudMessage instead
 */
class MessageToTopic extends CloudMessage
{
    /** @var Topic */
    private $topic;

    /**
     * @deprecated 4.14 Use CloudMessage::withTarget('topic', $topic) instead
     * @see CloudMessage::withTarget()
     *
     * @param Topic|string $topic
     *
     * @return MessageToTopic
     */
    public static function create($topic): self
    {
        \trigger_error(
            __METHOD__.' is deprecated. Use \Kreait\Firebase\CloudMessage::withTarget() instead.',
            \E_USER_DEPRECATED
        );

        $topic = $topic instanceof Topic ? $topic : Topic::fromValue($topic);

        $message = static::withTarget('condition', $topic->value());
        $message->topic = $topic;

        return $message;
    }

    /**
     * @deprecated 4.14 Use CloudMessage::fromArray() instead
     * @see CloudMessage::fromArray()
     *
     * @throws InvalidArgumentException
     *
     * @return MessageToTopic
     */
    public static function fromArray(array $data): self
    {
        \trigger_error(
            __METHOD__.' is deprecated. Use \Kreait\Firebase\CloudMessage::fromArray() instead.',
            \E_USER_DEPRECATED
        );

        if (!($topic = $data['topic'] ?? null)) {
            throw new InvalidArgumentException('Missing field "topic"');
        }

        $topic = $topic instanceof Topic ? $topic : Topic::fromValue((string) $topic);

        $message = parent::fromArray($data);
        $message->topic = $topic;

        return $message;
    }

    /**
     * @deprecated 4.29.0 Use CloudMessage instead
     */
    public function topic(): string
    {
        return (string) $this->topic;
    }
}

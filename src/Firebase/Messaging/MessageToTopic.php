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

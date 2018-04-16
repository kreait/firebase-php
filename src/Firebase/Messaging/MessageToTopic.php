<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;

class MessageToTopic implements Message
{
    use MessageTrait;

    /**
     * @var string
     */
    private $topic;

    private function __construct(string $topic)
    {
        $this->topic = $topic;
    }

    public static function create(string $topic): self
    {
        return new self($topic);
    }

    /**
     * @param array $data
     *
     * @throws InvalidArgumentException
     *
     * @return MessageToTopic
     */
    public static function fromArray(array $data): self
    {
        if (!array_key_exists('topic', $data)) {
            throw new InvalidArgumentException('Missing field "topic"');
        }

        $message = self::create($data['topic']);

        if ($data['data'] ?? null) {
            $message = $message->withData($data['data']);
        }

        if ($data['notification'] ?? null) {
            $message = $message->withNotification(Notification::fromArray($data['notification']));
        }

        return $message;
    }

    public function topic(): string
    {
        return $this->topic;
    }

    public function jsonSerialize()
    {
        return array_filter([
            'topic' => $this->topic,
            'data' => $this->data,
            'notification' => $this->notification,
        ]);
    }
}

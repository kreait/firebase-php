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

    public static function fromArray(array $data): self
    {
        if (!array_key_exists('topic', $data)) {
            throw new InvalidArgumentException('Missing field "topic"');
        }

        try {
            $message = new self($data['topic']);

            if ($data['data'] ?? null) {
                $message = $message->withData($data['data']);
            }

            if ($data['notification']) {
                $message = $message->withNotification(Notification::fromArray($data['notification']));
            }
        } catch (\Throwable $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }

        return $message;
    }

    public function jsonSerialize()
    {
        return [
            'message' => array_filter([
                'topic' => $this->topic,
                'data' => $this->data,
                'notification' => $this->notification,
            ]),
        ];
    }
}

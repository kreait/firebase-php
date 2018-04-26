<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;

class ConditionalMessage implements Message
{
    use MessageTrait;

    /**
     * @var string
     */
    private $condition;

    private function __construct(string $condition)
    {
        $this->condition = $condition;
    }

    public static function create(string $condition): self
    {
        return new self($condition);
    }

    /**
     * @param array $data
     *
     * @throws InvalidArgumentException
     *
     * @return ConditionalMessage
     */
    public static function fromArray(array $data): self
    {
        if (!array_key_exists('condition', $data)) {
            throw new InvalidArgumentException('Missing field "condition"');
        }

        $message = new self($data['condition']);

        if ($data['data'] ?? null) {
            $message = $message->withData($data['data']);
        }

        if ($data['notification'] ?? null) {
            $message = $message->withNotification(Notification::fromArray($data['notification']));
        }

        return $message;
    }

    public function condition(): string
    {
        return $this->condition;
    }

    public function jsonSerialize()
    {
        return array_filter([
            'condition' => $this->condition,
            'data' => $this->data,
            'notification' => $this->notification,
            'android' => $this->androidConfig,
            'apns' => $this->apnsConfig,
            'webpush' => $this->webPushConfig
        ]);
    }
}

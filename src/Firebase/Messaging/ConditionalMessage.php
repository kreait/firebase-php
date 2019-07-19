<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;

/**
 * @deprecated 4.14 Use CloudMessage instead
 */
class ConditionalMessage implements Message
{
    use MessageTrait;

    /**
     * @var Condition
     */
    private $condition;

    private function __construct(Condition $condition)
    {
        $this->condition = $condition;
    }

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

        return new self($condition);
    }

    /**
     * @deprecated 4.14 Use CloudMessage::fromArray() instead
     * @see CloudMessage::fromArray()
     *
     * @throws InvalidArgumentException
     *
     * @return ConditionalMessage
     */
    public static function fromArray(array $data): self
    {
        if (!\array_key_exists('condition', $data)) {
            throw new InvalidArgumentException('Missing field "condition"');
        }

        $message = self::create($data['condition']);

        if ($data['data'] ?? null) {
            $message = $message->withData($data['data']);
        }

        if ($data['notification'] ?? null) {
            $message = $message->withNotification(Notification::fromArray($data['notification']));
        }

        if ($data['android'] ?? null) {
            $message = $message->withAndroidConfig(AndroidConfig::fromArray($data['android']));
        }

        if ($data['apns'] ?? null) {
            $message = $message->withApnsConfig(ApnsConfig::fromArray($data['apns']));
        }

        if ($data['webpush'] ?? null) {
            $message = $message->withWebPushConfig(WebPushConfig::fromArray($data['webpush']));
        }

        if ($data['fcm_options'] ?? null) {
            $message = $message->withFcmOptions(FcmOptions::fromArray($data['fcm_options']));
        }

        return $message;
    }

    public function condition(): string
    {
        return (string) $this->condition;
    }

    public function jsonSerialize()
    {
        return \array_filter([
            'condition' => $this->condition,
            'data' => $this->data,
            'notification' => $this->notification,
            'android' => $this->androidConfig,
            'apns' => $this->apnsConfig,
            'webpush' => $this->webPushConfig,
            'fcm_options' => $this->fcmOptions,
        ]);
    }
}

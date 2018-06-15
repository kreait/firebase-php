<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;

class MessageToTopic extends Message
{
    /**
     * @var Topic
     */
    private $topic;

    private function __construct(Topic $topic)
    {
        $this->topic = $topic;
    }

    public static function create($topic): self
    {
        $topic = $topic instanceof Topic ? $topic : Topic::fromValue($topic);

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

        if ($data['android'] ?? null) {
            $message = $message->withAndroidConfig(AndroidConfig::fromArray($data['android']));
        }

        if ($data['apns'] ?? null) {
            $message = $message->withApnsConfig(ApnsConfig::fromArray($data['apns']));
        }

        if ($data['webpush'] ?? null) {
            $message = $message->withWebPushConfig(WebPushConfig::fromArray($data['webpush']));
        }

        return $message;
    }

    public function topic(): string
    {
        // TODO Change this to return a Topic instance in 5.0
        return (string) $this->topic;
    }

    public function jsonSerialize()
    {
        return array_filter([
            'topic' => $this->topic,
            'data' => $this->data,
            'notification' => $this->notification,
            'android' => $this->androidConfig,
            'apns' => $this->apnsConfig,
            'webpush' => $this->webPushConfig,
        ]);
    }
}

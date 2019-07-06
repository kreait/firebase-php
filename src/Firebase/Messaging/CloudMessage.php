<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Exception\Messaging\InvalidArgument;

class CloudMessage implements Message
{
    /**
     * @var MessageTarget|null
     */
    private $target;

    /**
     * @var MessageData|null
     */
    private $data;

    /**
     * @var Notification|null
     */
    private $notification;

    /**
     * @var AndroidConfig|null
     */
    private $androidConfig;

    /**
     * @var ApnsConfig|null
     */
    private $apnsConfig;

    /**
     * @var WebPushConfig|null
     */
    private $webPushConfig;

    private function __construct()
    {
    }

    /**
     * @param string $type One of "condition", "token", "topic"
     *
     * @throws InvalidArgumentException if the target type or value is invalid
     *
     * @return CloudMessage
     */
    public static function withTarget(string $type, string $value): self
    {
        return (new self())->withChangedTarget($type, $value);
    }

    public static function new(): self
    {
        return new self();
    }

    public static function fromArray(array $data): self
    {
        $new = new self();

        if (\count(\array_intersect(\array_keys($data), MessageTarget::TYPES)) > 1) {
            throw new InvalidArgument(
                'A message can only have one of the following targets: '
                .\implode(', ', MessageTarget::TYPES)
            );
        }

        if ($targetValue = $data[MessageTarget::CONDITION] ?? null) {
            $new = $new->withChangedTarget(MessageTarget::CONDITION, (string) $targetValue);
        } elseif ($targetValue = $data[MessageTarget::TOKEN] ?? null) {
            $new = $new->withChangedTarget(MessageTarget::TOKEN, (string) $targetValue);
        } elseif ($targetValue = $data[MessageTarget::TOPIC] ?? null) {
            $new = $new->withChangedTarget(MessageTarget::TOPIC, (string) $targetValue);
        }

        if ($data['data'] ?? null) {
            $new = $new->withData($data['data']);
        }

        if ($data['notification'] ?? null) {
            $new = $new->withNotification(Notification::fromArray($data['notification']));
        }

        if ($data['android'] ?? null) {
            $new = $new->withAndroidConfig(AndroidConfig::fromArray($data['android']));
        }

        if ($data['apns'] ?? null) {
            $new = $new->withApnsConfig(ApnsConfig::fromArray($data['apns']));
        }

        if ($data['webpush'] ?? null) {
            $new = $new->withWebPushConfig(WebPushConfig::fromArray($data['webpush']));
        }

        return $new;
    }

    /**
     * @param string $type One of "condition", "token", "topic"
     *
     * @throws InvalidArgumentException if the target type or value is invalid
     *
     * @return CloudMessage
     */
    public function withChangedTarget(string $type, string $value): self
    {
        $new = clone $this;
        $new->target = MessageTarget::with($type, $value);

        return $new;
    }

    /**
     * @param MessageData|array $data
     *
     * @throws InvalidArgumentException
     *
     * @return CloudMessage
     */
    public function withData($data): self
    {
        $new = clone $this;
        $new->data = $data instanceof MessageData ? $data : MessageData::fromArray($data);

        return $new;
    }

    /**
     * @param Notification|array $notification
     *
     * @throws InvalidArgumentException
     *
     * @return CloudMessage
     */
    public function withNotification($notification): self
    {
        $new = clone $this;
        $new->notification = $notification instanceof Notification ? $notification : Notification::fromArray($notification);

        return $new;
    }

    /**
     * @param AndroidConfig|array $config
     *
     * @throws InvalidArgumentException
     *
     * @return CloudMessage
     */
    public function withAndroidConfig($config): self
    {
        $new = clone $this;
        $new->androidConfig = $config instanceof AndroidConfig ? $config : AndroidConfig::fromArray($config);

        return $new;
    }

    /**
     * @param ApnsConfig|array $config
     *
     * @throws InvalidArgumentException
     *
     * @return CloudMessage
     */
    public function withApnsConfig($config): self
    {
        $new = clone $this;
        $new->apnsConfig = $config instanceof ApnsConfig ? $config : ApnsConfig::fromArray($config);

        return $new;
    }

    public function withWebPushConfig($config): self
    {
        $new = clone $this;
        $new->webPushConfig = $config instanceof WebPushConfig ? $config : WebPushConfig::fromArray($config);

        return $new;
    }

    public function hasTarget(): bool
    {
        return $this->target ? true : false;
    }

    public function jsonSerialize()
    {
        $data = [
            'data' => $this->data,
            'notification' => $this->notification,
            'android' => $this->androidConfig,
            'apns' => $this->apnsConfig,
            'webpush' => $this->webPushConfig,
        ];

        if ($this->target) {
            $data[$this->target->type()] = $this->target->value();
        }

        return \array_filter($data);
    }
}

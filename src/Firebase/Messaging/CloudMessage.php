<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Exception\Messaging\InvalidArgument;

final class CloudMessage implements Message
{
    private ?MessageTarget $target = null;
    private ?MessageData $data = null;
    private ?Notification $notification = null;
    private ?AndroidConfig $androidConfig = null;
    private ?ApnsConfig $apnsConfig = null;
    private ?WebPushConfig $webPushConfig = null;
    private ?FcmOptions $fcmOptions = null;

    private function __construct()
    {
    }

    /**
     * @param string $type One of "condition", "token", "topic"
     *
     * @throws InvalidArgumentException if the target type or value is invalid
     */
    public static function withTarget(string $type, string $value): self
    {
        return self::new()->withChangedTarget($type, $value);
    }

    public static function new(): self
    {
        return new self();
    }

    /**
     * @param array<string, mixed> $data
     */
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
            $new = $new->withNotification($data['notification']);
        }

        if ($data['android'] ?? null) {
            $new = $new->withAndroidConfig($data['android']);
        }

        if ($data['apns'] ?? null) {
            $new = $new->withApnsConfig(ApnsConfig::fromArray($data['apns']));
        }

        if ($data['webpush'] ?? null) {
            $new = $new->withWebPushConfig($data['webpush']);
        }

        if ($data['fcm_options'] ?? null) {
            $new = $new->withFcmOptions($data['fcm_options']);
        }

        return $new;
    }

    /**
     * @param string $type One of "condition", "token", "topic"
     *
     * @throws InvalidArgumentException if the target type or value is invalid
     */
    public function withChangedTarget(string $type, string $value): self
    {
        $new = clone $this;
        $new->target = MessageTarget::with($type, $value);

        return $new;
    }

    /**
     * @param MessageData|array<string, string> $data
     *
     * @throws InvalidArgumentException
     */
    public function withData($data): self
    {
        $new = clone $this;
        $new->data = $data instanceof MessageData ? $data : MessageData::fromArray($data);

        return $new;
    }

    /**
     * @param Notification|array{
     *     title: ?string,
     *     body: ?string,
     *     image: ?string
     * } $notification
     *
     * @throws InvalidArgumentException
     */
    public function withNotification($notification): self
    {
        $new = clone $this;
        $new->notification = $notification instanceof Notification ? $notification : Notification::fromArray($notification);

        return $new;
    }

    /**
     * @param AndroidConfig|array<string, mixed> $config
     *
     * @throws InvalidArgumentException
     */
    public function withAndroidConfig($config): self
    {
        $new = clone $this;
        $new->androidConfig = $config instanceof AndroidConfig ? $config : AndroidConfig::fromArray($config);

        return $new;
    }

    /**
     * @param ApnsConfig|array<string, mixed> $config
     *
     * @throws InvalidArgumentException
     */
    public function withApnsConfig($config): self
    {
        $new = clone $this;
        $new->apnsConfig = $config instanceof ApnsConfig ? $config : ApnsConfig::fromArray($config);

        return $new;
    }

    /**
     * @param WebPushConfig|array<string, mixed> $config
     */
    public function withWebPushConfig($config): self
    {
        $new = clone $this;
        $new->webPushConfig = $config instanceof WebPushConfig ? $config : WebPushConfig::fromArray($config);

        return $new;
    }

    /**
     * @param FcmOptions|array<string, mixed> $options
     */
    public function withFcmOptions($options): self
    {
        $new = clone $this;
        $new->fcmOptions = $options instanceof FcmOptions ? $options : FcmOptions::fromArray($options);

        return $new;
    }

    /**
     * Enables default notifications sounds on iOS and Android devices. WebPush doesn't support sounds.
     */
    public function withDefaultSounds(): self
    {
        $new = clone $this;
        $new->apnsConfig = ($new->apnsConfig ?: ApnsConfig::new())->withDefaultSound();
        $new->androidConfig = ($new->androidConfig ?: AndroidConfig::new())->withDefaultSound();

        return $new;
    }

    public function withLowestPossiblePriority(): self
    {
        $new = clone $this;
        $new->apnsConfig = ($new->apnsConfig ?: ApnsConfig::new())->withPowerConservingPriority();
        $new->androidConfig = ($new->androidConfig ?: AndroidConfig::new())->withNormalPriority();
        $new->webPushConfig = ($new->webPushConfig ?: WebPushConfig::new())->withVeryLowUrgency();

        return $new;
    }

    public function withHighestPossiblePriority(): self
    {
        $new = clone $this;
        $new->apnsConfig = ($new->apnsConfig ?: ApnsConfig::new())->withImmediatePriority();
        $new->androidConfig = ($new->androidConfig ?: AndroidConfig::new())->withHighPriority();
        $new->webPushConfig = ($new->webPushConfig ?: WebPushConfig::new())->withHighUrgency();

        return $new;
    }

    public function hasTarget(): bool
    {
        return (bool) $this->target;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $data = [
            'data' => $this->data,
            'notification' => $this->notification,
            'android' => $this->androidConfig,
            'apns' => $this->apnsConfig,
            'webpush' => $this->webPushConfig,
            'fcm_options' => $this->fcmOptions,
        ];

        if ($this->target) {
            $data[$this->target->type()] = $this->target->value();
        }

        return \array_filter($data, static function ($value) {
            return $value !== null
                && !($value instanceof MessageData && $value->jsonSerialize() === []);
        });
    }
}

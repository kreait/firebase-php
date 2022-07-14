<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Beste\Json;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Exception\Messaging\InvalidArgument;

/**
 * @see https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages
 *
 * @phpstan-import-type AndroidConfigShape from AndroidConfig
 * @phpstan-import-type ApnsConfigShape from ApnsConfig
 * @phpstan-import-type FcmOptionsShape from FcmOptions
 * @phpstan-import-type WebPushConfigShape from WebPushConfig
 */
final class CloudMessage implements Message
{
    private MessageTarget $target;
    private MessageData $data;
    private Notification $notification;
    private AndroidConfig $androidConfig;
    private ApnsConfig $apnsConfig;
    private WebPushConfig $webPushConfig;
    private FcmOptions $fcmOptions;

    private function __construct(
        MessageTarget $messageTarget
    ) {
        $this->target = $messageTarget;
        $this->data = MessageData::fromArray([]);
        $this->notification = Notification::fromArray([]);
        $this->androidConfig = AndroidConfig::fromArray([]);
        $this->apnsConfig = ApnsConfig::fromArray([]);
        $this->webPushConfig = WebPushConfig::fromArray([]);
        $this->fcmOptions = FcmOptions::fromArray([]);
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
        return new self(MessageTarget::with(MessageTarget::UNKNOWN, ''));
    }

    /**
     * @param array{
     *     token?: non-empty-string,
     *     topic?: non-empty-string,
     *     condition?: non-empty-string,
     *     data?: MessageData|array<string, string>,
     *     notification?: Notification|array{
     *         title?: string,
     *         body?: string,
     *         image?: string
     *     },
     *     android?: AndroidConfigShape,
     *     apns?: ApnsConfig|ApnsConfigShape,
     *     webpush?: WebPushConfig|WebPushConfigShape,
     *     fcm_options?: FcmOptions|FcmOptionsShape
     * } $data
     */
    public static function fromArray(array $data): self
    {
        if (\count(\array_intersect(\array_keys($data), MessageTarget::TYPES)) > 1) {
            throw new InvalidArgument(
                'A message can only have one of the following targets: '
                .\implode(', ', MessageTarget::TYPES)
            );
        }

        $new = new self(self::determineTargetFromArray($data));

        if ($messageData = ($data['data'] ?? null)) {
            $new = $new->withData($messageData);
        }

        if ($notification = ($data['notification'] ?? null)) {
            $new = $new->withNotification($notification);
        }

        if ($androidConfig = ($data['android'] ?? null)) {
            $new = $new->withAndroidConfig($androidConfig);
        }

        if ($apnsConfig = ($data['apns'] ?? null)) {
            $new = $new->withApnsConfig($apnsConfig);
        }

        if ($webPushConfig = ($data['webpush'] ?? null)) {
            $new = $new->withWebPushConfig($webPushConfig);
        }

        if ($fcmOptions = ($data['fcm_options'] ?? null)) {
            $new = $new->withFcmOptions($fcmOptions);
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
     * @param MessageData|array<array-key, mixed> $data
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
     *     title?: string,
     *     body?: string,
     *     image?: string
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
     * @param AndroidConfig|AndroidConfigShape $config
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
     * @param ApnsConfig|ApnsConfigShape $config
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
     * @param WebPushConfig|WebPushConfigShape $config
     */
    public function withWebPushConfig($config): self
    {
        $new = clone $this;
        $new->webPushConfig = $config instanceof WebPushConfig ? $config : WebPushConfig::fromArray($config);

        return $new;
    }

    /**
     * @param FcmOptions|FcmOptionsShape $options
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
        $new->apnsConfig = $this->apnsConfig->withDefaultSound();
        $new->androidConfig = $this->androidConfig->withDefaultSound();

        return $new;
    }

    public function withLowestPossiblePriority(): self
    {
        $new = clone $this;
        $new->apnsConfig = $this->apnsConfig->withPowerConservingPriority();
        $new->androidConfig = $this->androidConfig->withNormalMessagePriority();
        $new->webPushConfig = $this->webPushConfig->withVeryLowUrgency();

        return $new;
    }

    public function withHighestPossiblePriority(): self
    {
        $new = clone $this;
        $new->apnsConfig = $this->apnsConfig->withImmediatePriority();
        $new->androidConfig = $this->androidConfig->withHighMessagePriority();
        $new->webPushConfig = $this->webPushConfig->withHighUrgency();

        return $new;
    }

    public function hasTarget(): bool
    {
        return $this->target->type() !== MessageTarget::UNKNOWN;
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

        $data = Json::decode(Json::encode($data), true);

        if ($this->target->type() !== MessageTarget::UNKNOWN) {
            $data[$this->target->type()] = $this->target->value();
        }

        return \array_filter(
            $data,
            static fn ($value) => $value !== null && $value !== []
        );
    }


    /**
     * @param array{
     *     token?: string,
     *     topic?: string,
     *     condition?: string
     * } $data
     */
    private static function determineTargetFromArray(array $data): MessageTarget
    {
        if ($targetValue = $data[MessageTarget::CONDITION] ?? null) {
            return MessageTarget::with(MessageTarget::CONDITION, $targetValue);
        }

        if ($targetValue = $data[MessageTarget::TOKEN] ?? null) {
            return MessageTarget::with(MessageTarget::TOKEN, $targetValue);
        }

        if ($targetValue = $data[MessageTarget::TOPIC] ?? null) {
            return MessageTarget::with(MessageTarget::TOPIC, $targetValue);
        }

        return MessageTarget::with(MessageTarget::UNKNOWN, '');
    }
}

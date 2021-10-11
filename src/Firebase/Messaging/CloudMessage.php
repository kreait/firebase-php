<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Exception\Messaging\InvalidArgument;

/**
 * @see https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages
 */
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
     * @param array{
     *     token?: string,
     *     topic?: string,
     *     condition?: string,
     *     data?: MessageData|array<string, string>,
     *     notification?: Notification|array{
     *         title?: string,
     *         body?: string,
     *         image?: string
     *     },
     *     android?: array{
     *         collapse_key?: string,
     *         priority?: 'normal'|'high',
     *         ttl?: string,
     *         restricted_package_name?: string,
     *         data?: array<string, string>,
     *         notification?: array<string, string>,
     *         fcm_options?: array<string, mixed>,
     *         direct_boot_ok?: bool
     *     },
     *     apns?: ApnsConfig|array{
     *          headers?: array<string, string>,
     *          payload?: array<string, mixed>,
     *          fcm_options?: array{
     *              analytics_label?: string,
     *              image?: string
     *          }
     *     },
     *     webpush?: WebPushConfig|array{
     *         headers?: array<string, string>,
     *         data?: array<string, string>,
     *         notification?: array<string, mixed>,
     *         fcm_options?: array{
     *             link?: string,
     *             analytics_label?: string
     *         }
     *     },
     *     fcm_options?: FcmOptions|array{
     *         analytics_label?: string
     *     }
     * } $data
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
            $new = $new->withChangedTarget(MessageTarget::CONDITION, $targetValue);
        } elseif ($targetValue = $data[MessageTarget::TOKEN] ?? null) {
            $new = $new->withChangedTarget(MessageTarget::TOKEN, $targetValue);
        } elseif ($targetValue = $data[MessageTarget::TOPIC] ?? null) {
            $new = $new->withChangedTarget(MessageTarget::TOPIC, $targetValue);
        }

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
     * @param AndroidConfig|array{
     *     collapse_key?: string,
     *     priority?: 'normal'|'high',
     *     ttl?: string,
     *     restricted_package_name?: string,
     *     data?: array<string, string>,
     *     notification?: array<string, string>,
     *     fcm_options?: array<string, mixed>,
     *     direct_boot_ok?: bool
     * } $config
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
     * @param ApnsConfig|array{
     *     headers?: array<string, string>,
     *     payload?: array<string, mixed>,
     *     fcm_options?: array{
     *         analytics_label?: string,
     *         image?: string
     *     }
     * } $config
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
     * @param WebPushConfig|array{
     *     headers?: array<string, string>,
     *     data?: array<string, string>,
     *     notification?: array<string, mixed>,
     *     fcm_options?: array{
     *         link?: string,
     *         analytics_label?: string
     *     }
     * } $config
     */
    public function withWebPushConfig($config): self
    {
        $new = clone $this;
        $new->webPushConfig = $config instanceof WebPushConfig ? $config : WebPushConfig::fromArray($config);

        return $new;
    }

    /**
     * @param FcmOptions|array{
     *     analytics_label?: string
     * } $options
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

        if ($this->target !== null) {
            $data[$this->target->type()] = $this->target->value();
        }

        return \array_filter(
            $data,
            static fn ($value) => $value !== null && !($value instanceof MessageData && $value->jsonSerialize() === [])
        );
    }
}

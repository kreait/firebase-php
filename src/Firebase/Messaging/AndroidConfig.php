<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use JsonSerializable;

/**
 * @see https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#androidconfig
 * @see https://firebase.google.com/docs/cloud-messaging/concept-options#setting-the-priority-of-a-message
 */
final class AndroidConfig implements JsonSerializable
{
    /** @var array{
     *      collapse_key?: string,
     *      priority?: self::PRIORITY_*,
     *      ttl?: string,
     *      restricted_package_name?: string,
     *      data?: array<string, string>,
     *      notification?: array<string, string>,
     *      fcm_options?: array<string, mixed>,
     *      direct_boot_ok?: bool
     * }
     */
    private const MESSAGE_PRIORITY_NORMAL = 'normal';
    private const MESSAGE_PRIORITY_HIGH = 'high';

    private const NOTIFICATION_PRIORITY_UNSPECIFIED = 'PRIORITY_UNSPECIFIED';
    private const NOTIFICATION_PRIORITY_MIN = 'PRIORITY_MIN';
    private const NOTIFICATION_PRIORITY_LOW = 'PRIORITY_LOW';
    private const NOTIFICATION_PRIORITY_DEFAULT = 'PRIORITY_DEFAULT';
    private const NOTIFICATION_PRIORITY_HIGH = 'PRIORITY_HIGH';
    private const NOTIFICATION_PRIORITY_MAX = 'PRIORITY_MAX';

    private const NOTIFICATION_VISIBILITY_PRIVATE = 'PRIVATE';
    private const NOTIFICATION_VISIBILITY_PUBLIC = 'PUBLIC';
    private const NOTIFICATION_VISIBILITY_SECRET = 'SECRET';

    /** @var AndroidConfigShape */
    private array $config;

    /**
     * @param array{
     *     collapse_key?: string,
     *     priority?: self::PRIORITY_*,
     *     ttl?: string,
     *     restricted_package_name?: string,
     *     data?: array<string, string>,
     *     notification?: array<string, string>,
     *     fcm_options?: array<string, mixed>,
     *     direct_boot_ok?: bool
     * } $config
     */
    private function __construct(array $config)
    {
        $this->config = $config;
    }

    public static function new(): self
    {
        return new self([]);
    }

    /**
     * @param array{
     *     collapse_key?: string,
     *     priority?: self::PRIORITY_*,
     *     ttl?: string,
     *     restricted_package_name?: string,
     *     data?: array<string, string>,
     *     notification?: array<string, string>,
     *     fcm_options?: array<string, mixed>,
     *     direct_boot_ok?: bool
     * } $config
     */
    public static function fromArray(array $config): self
    {
        return new self($config);
    }

    public function withDefaultSound(): self
    {
        return $this->withSound('default');
    }

    /**
     * The sound to play when the device receives the notification. Supports "default" or the filename
     * of a sound resource bundled in the app. Sound files must reside in /res/raw/.
     */
    public function withSound(string $sound): self
    {
        $config = clone $this;
        $config->config['notification'] ??= [];
        $config->config['notification']['sound'] = $sound;

        return $config;
    }

    /**
     * @deprecated 6.4.0 Use {@see withHighMessagePriority()} instead
     */
    public function withHighPriority(): self
    {
        return $this->withMessagePriority(self::MESSAGE_PRIORITY_HIGH);
    }

    public function withHighMessagePriority(): self
    {
        return $this->withMessagePriority(self::MESSAGE_PRIORITY_HIGH);
    }

    /**
     * @deprecated 6.4.0 Use {@see withNormalMessagePriority()} instead
     */
    public function withNormalPriority(): self
    {
        return $this->withMessagePriority(self::MESSAGE_PRIORITY_NORMAL);
    }

    public function withNormalMessagePriority(): self
    {
        return $this->withMessagePriority(self::MESSAGE_PRIORITY_NORMAL);
    }

    /**
     * @deprecated 6.4.0 Use {@see withMessagePriority()} instead
     *
     * @param self::MESSAGE_PRIORITY_* $priority
     */
    public function withPriority(string $priority): self
    {
        return $this->withMessagePriority($priority);
    }

    /**
     * @param self::MESSAGE_PRIORITY_* $messagePriority
     */
    public function withMessagePriority(string $messagePriority): self
    {
        $config = clone $this;
        $config->config['priority'] = $messagePriority;

        return $config;
    }

    public function withMinimalNotificationPriority(): self
    {
        return $this->withNotificationPriority(self::NOTIFICATION_PRIORITY_MIN);
    }

    public function withLowNotificationPriority(): self
    {
        return $this->withNotificationPriority(self::NOTIFICATION_PRIORITY_LOW);
    }

    public function withDefaultNotificationPriority(): self
    {
        return $this->withNotificationPriority(self::NOTIFICATION_PRIORITY_DEFAULT);
    }

    public function withHighNotificationPriority(): self
    {
        return $this->withNotificationPriority(self::NOTIFICATION_PRIORITY_HIGH);
    }

    public function withMaximalNotificationPriority(): self
    {
        return $this->withNotificationPriority(self::NOTIFICATION_PRIORITY_MAX);
    }

    public function withUnspecifiedNotificationPriority(): self
    {
        return $this->withNotificationPriority(self::NOTIFICATION_PRIORITY_UNSPECIFIED);
    }

    /**
     * @param self::NOTIFICATION_PRIORITY_* $notificationPriority
     */
    public function withNotificationPriority(string $notificationPriority): self
    {
        $config = clone $this;

        $config->config['notification'] ??= [];
        $config->config['notification']['notification_priority'] = $notificationPriority;

        return $config;
    }

    public function withPrivateNotificationVisibility(): self
    {
        return $this->withNotificationVisibility(self::NOTIFICATION_VISIBILITY_PRIVATE);
    }

    public function withPublicNotificationVisibility(): self
    {
        return $this->withNotificationVisibility(self::NOTIFICATION_VISIBILITY_PUBLIC);
    }

    public function withSecretNotificationVisibility(): self
    {
        return $this->withNotificationVisibility(self::NOTIFICATION_VISIBILITY_SECRET);
    }

    /**
     * @param self::NOTIFICATION_VISIBILITY_* $notificationVisibility
     */
    public function withNotificationVisibility(string $notificationVisibility): self
    {
        $config = clone $this;

        $config->config['notification'] ??= [];
        $config->config['notification']['visibility'] = $notificationVisibility;

        return $config;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return \array_filter($this->config, static fn ($value) => $value !== null && $value !== []);
    }
}

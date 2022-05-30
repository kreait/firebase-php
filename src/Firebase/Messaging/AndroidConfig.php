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

        $config = clone $this;
        $config->config['priority'] = $priority;

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

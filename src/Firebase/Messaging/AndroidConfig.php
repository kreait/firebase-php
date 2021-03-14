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
    private const PRIORITY_NORMAL = 'normal';
    private const PRIORITY_HIGH = 'high';

    /** @var array<string, mixed> */
    private $config;

    private function __construct()
    {
    }

    public static function new(): self
    {
        return self::fromArray([]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $config = new self();
        $config->config = $data;

        return $config;
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
        $config->config['notification'] = $config->config['notification'] ?? [];
        $config->config['notification']['sound'] = $sound;

        return $config;
    }

    public function withHighPriority(): self
    {
        return $this->withPriority(self::PRIORITY_HIGH);
    }

    public function withNormalPriority(): self
    {
        return $this->withPriority(self::PRIORITY_NORMAL);
    }

    public function withPriority(string $priority): self
    {
        $config = clone $this;
        $config->config['priority'] = $priority;

        return $config;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return \array_filter($this->config, static function ($value) {
            return $value !== null;
        });
    }
}

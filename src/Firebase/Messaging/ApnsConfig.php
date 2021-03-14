<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use JsonSerializable;

/**
 * @see https://developer.apple.com/documentation/usernotifications/setting_up_a_remote_notification_server/generating_a_remote_notification
 * @see https://developer.apple.com/documentation/usernotifications/setting_up_a_remote_notification_server/sending_notification_requests_to_apns
 */
final class ApnsConfig implements JsonSerializable
{
    private const PRIORITY_CONSERVE_POWER = '5';
    private const PRIORITY_IMMEDIATE = '10';

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
     * The name of a sound file in your app’s main bundle or in the Library/Sounds folder of your app’s
     * container directory. Specify the string "default" to play the system sound.
     */
    public function withSound(string $sound): self
    {
        $config = clone $this;

        $config->config['payload'] = $config->config['payload'] ?? [];
        $config->config['payload']['aps'] = $config->config['payload']['aps'] ?? [];
        $config->config['payload']['aps']['sound'] = $sound;

        return $config;
    }

    /**
     * The number to display in a badge on your app’s icon. Specify 0 to remove the current badge, if any.
     */
    public function withBadge(int $number): self
    {
        $config = clone $this;
        $config->config['payload'] = $config->config['payload'] ?? [];
        $config->config['payload']['aps'] = $config->config['payload']['aps'] ?? [];
        $config->config['payload']['aps']['badge'] = $number;

        return $config;
    }

    public function withImmediatePriority(): self
    {
        return $this->withPriority(self::PRIORITY_IMMEDIATE);
    }

    public function withPowerConservingPriority(): self
    {
        return $this->withPriority(self::PRIORITY_CONSERVE_POWER);
    }

    public function withPriority(string $priority): self
    {
        $config = clone $this;
        $config->config['headers'] = $config->config['headers'] ?? [];
        $config->config['headers']['apns-priority'] = $priority;

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

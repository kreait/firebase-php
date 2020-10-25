<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use JsonSerializable;

/**
 * @see https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#androidconfig
 */
final class AndroidConfig implements JsonSerializable
{
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

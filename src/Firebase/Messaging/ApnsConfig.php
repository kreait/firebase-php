<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use JsonSerializable;

/**
 * @see https://developer.apple.com/documentation/usernotifications/setting_up_a_remote_notification_server/generating_a_remote_notification
 * @see https://developer.apple.com/documentation/usernotifications/setting_up_a_remote_notification_server/sending_notification_requests_to_apns
 * @see https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#apnsconfig
 *
 * @phpstan-type ApnsConfigShape array{
 *     headers?: array<non-empty-string, non-empty-string>,
 *     payload?: array<non-empty-string, mixed>,
 *     fcm_options?: array{
 *         analytics_label?: string,
 *         image?: string
 *     }
 * }
 */
final class ApnsConfig implements JsonSerializable
{
    private const PRIORITY_CONSERVE_POWER = '5';
    private const PRIORITY_IMMEDIATE = '10';

    private bool $isBackgroundMessage = false;

    /** @var array<non-empty-string, non-empty-string> */
    private array $headers;

    /** @var array<non-empty-string, mixed> */
    private array $payload = [];

    /**
     * @var array{
     *     analytics_label?: string,
     *     image?: string
     * }
     */
    private array $fcmOptions = [];

    /**
     * @param array<non-empty-string, non-empty-string> $headers
     * @param array<non-empty-string, mixed> $payload
     * @param array<non-empty-string, string> $fcmOptions
     */
    private function __construct(array $headers, array $payload, array $fcmOptions)
    {
        $this->headers = $headers;
        $this->payload = $payload;
        $this->fcmOptions = $fcmOptions;
    }

    public static function new(): self
    {
        return new self([], [], []);
    }

    /**
     * @param ApnsConfigShape $data
     */
    public static function fromArray(array $data): self
    {
        $headers = $data['headers'] ?? [];
        $payload = $data['payload'] ?? [];
        $fcmOptions = $data['fcm_options'] ?? [];

        return new self($headers, $payload, $fcmOptions);
    }

    public function asBackgroundMessage(): self
    {
        $config = clone $this;

        $config->isBackgroundMessage = true;
        $config->payload['aps'] ??= [];
        $config->payload['aps']['content-available'] = 1;

        // Unset keys that need to be absent to qualify as a background message
        // @see https://developer.apple.com/documentation/usernotifications/setting_up_a_remote_notification_server/generating_a_remote_notification#2943363
        unset(
            $config->payload['aps']['alert'],
            $config->payload['aps']['badge'],
            $config->payload['aps']['sound'],
        );

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
        // @todo Throw an exception in the next major release that background messages can't have sounds.
        if ($this->isBackgroundMessage) {
            return $this;
        }

        $config = clone $this;

        $config->payload ??= [];
        $config->payload['aps'] ??= [];
        $config->payload['aps']['sound'] = $sound;

        return $config;
    }

    /**
     * The number to display in a badge on your app’s icon. Specify 0 to remove the current badge, if any.
     */
    public function withBadge(int $number): self
    {
        // @todo Throw an exception in the next major release that background messages can't have sounds.
        if ($this->isBackgroundMessage) {
            return $this;
        }

        $config = clone $this;
        $config->payload ??= [];
        $config->payload['aps'] ??= [];
        $config->payload['aps']['badge'] = $number;

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

    /**
     * @param non-empty-string $priority
     */
    public function withPriority(string $priority): self
    {
        $config = clone $this;

        $config->headers['apns-priority'] = $priority;

        return $config;
    }

    /**
     * A subtitle of the notification, supported by iOS 9+, silently ignored for others.
     */
    public function withSubtitle(string $subtitle): self
    {
        $config = clone $this;
        $config->payload['aps'] ??= [];
        $config->payload['aps']['subtitle'] = $subtitle;

        return $config;
    }

    /**
     * @return array<non-empty-string, mixed>
     */
    public function jsonSerialize(): array
    {
        $filter = static fn ($value): bool => $value !== null && $value !== [];

        return array_filter([
            'headers' => array_filter($this->headers, $filter),
            'payload' => array_filter($this->payload, $filter),
            'fcm_options' => array_filter($this->fcmOptions, $filter),
        ], $filter);
    }
}

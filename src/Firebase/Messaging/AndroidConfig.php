<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use JsonSerializable;
use Kreait\Firebase\Exception\Messaging\InvalidArgument;

use function array_filter;
use function array_key_exists;
use function is_int;
use function is_string;
use function preg_match;
use function sprintf;

/**
 * @see https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#androidconfig
 * @see https://firebase.google.com/docs/cloud-messaging/concept-options#setting-the-priority-of-a-message
 * @see https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#androidmessagepriority Android Message Priorities
 * @see https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#androidfcmoptions Android FCM Options Syntax
 *
 * @phpstan-type AndroidFcmOptionsShape array{
 *     analytics_label?: non-empty-string
 * }
 *
 * @see https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#LightSettings Android FCM Light Settings Syntax
 *
 * @phpstan-type AndroidFcmLightSettingsShape array{
 *     color: array{
 *         red: float,
 *         green: float,
 *         blue: float,
 *         alpha: float
 *     },
 *     light_on_duration: non-empty-string,
 *     light_off_duration: non-empty-string
 * }
 *
 * @see https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#AndroidNotification Android Notification Syntax
 *
 * @phpstan-type AndroidNotificationShape array{
 *     title?: non-empty-string,
 *     body?: non-empty-string,
 *     icon?: non-empty-string,
 *     color?: non-empty-string,
 *     sound?: non-empty-string,
 *     click_action?: non-empty-string,
 *     body_loc_key?: non-empty-string,
 *     body_loc_args?: list<non-empty-string>,
 *     title_loc_key?: non-empty-string,
 *     title_loc_args?: list<non-empty-string>,
 *     channel_id?: non-empty-string,
 *     ticker?: non-empty-string,
 *     sticky?: bool,
 *     event_time?: non-empty-string,
 *     local_only?: bool,
 *     notification_priority?: self::NOTIFICATION_PRIORITY_*,
 *     default_sound?: bool,
 *     default_vibrate_timings?: bool,
 *     default_light_settings?: bool,
 *     vibrate_timings?: list<non-empty-string>,
 *     visibility?: self::NOTIFICATION_VISIBILITY_*,
 *     notification_count?: positive-int,
 *     light_settings?: AndroidFcmLightSettingsShape,
 *     image?: non-empty-string
 * }
 *
 * @see https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#androidconfig Android Config Syntax
 *
 * @phpstan-type AndroidConfigShape array{
 *     collapse_key?: non-empty-string,
 *     priority?: self::MESSAGE_PRIORITY_*,
 *     ttl?: positive-int|non-empty-string|null,
 *     restricted_package_name?: non-empty-string,
 *     data?: array<non-empty-string, non-empty-string>,
 *     notification?: AndroidNotificationShape,
 *     fcm_options?: AndroidFcmOptionsShape,
 *     direct_boot_ok?: bool
 * }
 */
final class AndroidConfig implements JsonSerializable
{
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
     * @param AndroidConfigShape $config
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
     * @param AndroidConfigShape $config
     *
     * @throws InvalidArgument
     */
    public static function fromArray(array $config): self
    {
        if (array_key_exists('ttl', $config) && $config['ttl'] !== null) {
            $config['ttl'] = self::ensureValidTtl($config['ttl']);
        }

        return new self($config);
    }

    public function withDefaultSound(): self
    {
        return $this->withSound('default');
    }

    /**
     * The sound to play when the device receives the notification. Supports "default" or the filename
     * of a sound resource bundled in the app. Sound files must reside in /res/raw/.
     *
     * @param non-empty-string $sound
     */
    public function withSound(string $sound): self
    {
        $config = clone $this;

        $config->config['notification'] ??= [];
        $config->config['notification']['sound'] = $sound;

        return $config;
    }

    public function withHighMessagePriority(): self
    {
        return $this->withMessagePriority(self::MESSAGE_PRIORITY_HIGH);
    }

    public function withNormalMessagePriority(): self
    {
        return $this->withMessagePriority(self::MESSAGE_PRIORITY_NORMAL);
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

    public function jsonSerialize(): array
    {
        return array_filter($this->config, static fn ($value) => $value !== null && $value !== []);
    }

    /**
     * @param int|string $value
     *
     * @throws InvalidArgument
     *
     * @return non-empty-string
     */
    private static function ensureValidTtl($value): string
    {
        $expectedPattern = '/^\d+s$/';
        $errorMessage = "The TTL of an AndroidConfig must be an positive integer or string matching {$expectedPattern}";

        if (is_int($value) && $value >= 0) {
            return sprintf('%ds', $value);
        }

        if (!is_string($value) || $value === '') {
            throw new InvalidArgument($errorMessage);
        }

        if (preg_match('/^\d+$/', $value) === 1) {
            return sprintf('%ds', $value);
        }

        if (preg_match($expectedPattern, $value) === 1) {
            return $value;
        }

        throw new InvalidArgument($errorMessage);
    }
}

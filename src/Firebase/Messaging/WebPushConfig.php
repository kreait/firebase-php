<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use JsonSerializable;

/**
 * @see https://tools.ietf.org/html/rfc8030#section-5.3 Web Push Message Urgency
 *
 * @phpstan-type WebPushHeadersShape array{
 *     TTL?: positive-int,
 *     Urgency?: self::URGENCY_*
 * }
 *
 * @see https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#webpushfcmoptions WebPush FCM Options
 * @phpstan-type WebPushFcmOptionsShape array{
 *     link?: non-empty-string,
 *     analytics_label?: non-empty-string
 * }
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/API/Notification/Notification#syntax WebPush Notification Syntax
 * @phpstan-type WebPushNotificationShape array{
 *     title: non-empty-string,
 *     options?: array{
 *         dir?: 'ltr'|'rtl'|'auto',
 *         lang?: string,
 *         badge?: non-empty-string,
 *         body?: non-empty-string,
 *         tag?: non-empty-string,
 *         icon?: non-empty-string,
 *         image?: non-empty-string,
 *         data?: mixed,
 *         vibrate?: list<positive-int>,
 *         renotify?: bool,
 *         requireInteraction?: bool,
 *         actions?: list<array{
 *             action: non-empty-string,
 *             title: non-empty-string,
 *             icon: non-empty-string
 *         }>,
 *         silent?: bool
 *     }
 * }
 *
 * @see https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#webpushconfig WebPush Config Syntax
 * @phpstan-type WebPushConfigShape array{
 *     headers?: WebPushHeadersShape,
 *     data?: array<non-empty-string, non-empty-string>,
 *     notification?: WebPushNotificationShape,
 *     fcm_options?: WebPushFcmOptionsShape
 * }
 */
final class WebPushConfig implements JsonSerializable
{
    private const URGENCY_VERY_LOW = 'very-low';
    private const URGENCY_LOW = 'low';
    private const URGENCY_NORMAL = 'normal';
    private const URGENCY_HIGH = 'high';

    /**
     * @var WebPushConfigShape
     */
    private array $config;

    /**
     * @param WebPushConfigShape $config
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
     * @param WebPushConfigShape $config
     */
    public static function fromArray(array $config): self
    {
        return new self($config);
    }

    public function withHighUrgency(): self
    {
        return $this->withUrgency(self::URGENCY_HIGH);
    }

    public function withNormalUrgency(): self
    {
        return $this->withUrgency(self::URGENCY_NORMAL);
    }

    public function withLowUrgency(): self
    {
        return $this->withUrgency(self::URGENCY_LOW);
    }

    public function withVeryLowUrgency(): self
    {
        return $this->withUrgency(self::URGENCY_VERY_LOW);
    }

    /**
     * @param self::URGENCY_* $urgency
     */
    public function withUrgency(string $urgency): self
    {
        $config = clone $this;

        $config->config['headers'] ??= [];
        $config->config['headers']['Urgency'] = $urgency;

        return $config;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return \array_filter($this->config, static fn ($value) => $value !== null);
    }
}

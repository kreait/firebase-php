<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use JsonSerializable;
use Kreait\Firebase\Exception\Messaging\InvalidArgument;

use function array_filter;
use function array_key_exists;
use function implode;
use function in_array;
use function is_array;
use function is_int;
use function is_string;
use function preg_match;
use function sprintf;

/**
 * @see https://tools.ietf.org/html/rfc8030#section-5.3 Web Push Message Urgency
 *
 * @phpstan-type WebPushHeadersShape array{
 *     TTL?: positive-int|numeric-string|null,
 *     Urgency?: self::URGENCY_*
 * }
 *
 * @see https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#webpushfcmoptions WebPush FCM Options
 *
 * @phpstan-type WebPushFcmOptionsShape array{
 *     link?: non-empty-string,
 *     analytics_label?: non-empty-string
 * }
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/API/Notification/Notification#syntax WebPush Notification Syntax
 *
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
 *
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
    private const VALID_URGENCIES = [
        self::URGENCY_VERY_LOW,
        self::URGENCY_LOW,
        self::URGENCY_NORMAL,
        self::URGENCY_HIGH,
    ];

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
        if (array_key_exists('headers', $config) && is_array($config['headers'])) {
            $config['headers'] = self::ensureValidHeaders($config['headers']);

            if ($config['headers'] === []) {
                unset($config['headers']);
            }
        }

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
        return array_filter($this->config, static fn ($value) => $value !== null);
    }

    /**
     * @param WebPushHeadersShape $headers
     *
     * @return WebPushHeadersShape
     */
    private static function ensureValidHeaders(array $headers): array
    {
        if (array_key_exists('TTL', $headers)) {
            if (is_int($headers['TTL'])) {
                $headers['TTL'] = (string) $headers['TTL'];
            }

            if (is_string($headers['TTL']) && (preg_match('/^[1-9]\d*$/', $headers['TTL']) !== 1)) {
                throw new InvalidArgument('The TTL in the WebPushConfig must must be a positive int');
            }

            if ($headers['TTL'] === null) {
                unset($headers['TTL']);
            }
        }

        if (!array_key_exists('Urgency', $headers)) {
            return $headers;
        }

        if (in_array($headers['Urgency'], self::VALID_URGENCIES, true)) {
            return $headers;
        }

        throw new InvalidArgument(sprintf(
            'The Urgency in the WebPushConfig header must must be one of %s',
            implode(',', self::VALID_URGENCIES),
        ));
    }
}

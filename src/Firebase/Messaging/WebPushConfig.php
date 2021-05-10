<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use JsonSerializable;

/**
 * @see https://tools.ietf.org/html/rfc8030#section-5.3 Web Push Message Urgency
 * @see https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#webpushconfig
 */
final class WebPushConfig implements JsonSerializable
{
    private const URGENCY_VERY_LOW = 'very-low';
    private const URGENCY_LOW = 'low';
    private const URGENCY_NORMAL = 'normal';
    private const URGENCY_HIGH = 'high';

    /** @var array{
     *      headers?: array<string, string>,
     *      data?: array<string, string>,
     *      notification?: array<string, mixed>,
     *      fcm_options?: array{
     *          link?: string,
     *          analytics_label?: string
     *      }
     * }
     */
    private array $config;

    /**
     * @param array{
     *     headers?: array<string, string>,
     *     data?: array<string, string>,
     *     notification?: array<string, mixed>,
     *     fcm_options?: array{
     *         link?: string,
     *         analytics_label?: string
     *     }
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
     *     headers?: array<string, string>,
     *     data?: array<string, string>,
     *     notification?: array<string, mixed>,
     *     fcm_options?: array{
     *         link?: string,
     *         analytics_label?: string
     *     }
     * } $config
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

    public function withUrgency(string $urgency): self
    {
        $config = clone $this;
        $config->config['headers'] = $config->config['headers'] ?? [];
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

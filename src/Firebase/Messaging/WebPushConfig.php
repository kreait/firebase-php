<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use JsonSerializable;

/**
 * @see https://tools.ietf.org/html/rfc8030#section-5.3 Web Push Message Urgency
 */
final class WebPushConfig implements JsonSerializable
{
    private const URGENCY_VERY_LOW = 'very-low';
    private const URGENCY_LOW = 'low';
    private const URGENCY_NORMAL = 'normal';
    private const URGENCY_HIGH = 'high';

    /** @var array<string, mixed> */
    private array $rawConfig = [];

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
        $config->rawConfig = $data;

        return $config;
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
        $config->rawConfig['headers'] = $config->rawConfig['headers'] ?? [];
        $config->rawConfig['headers']['Urgency'] = $urgency;

        return $config;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return \array_filter($this->rawConfig, static fn ($value) => $value !== null);
    }
}

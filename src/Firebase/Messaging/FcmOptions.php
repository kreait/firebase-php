<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use JsonSerializable;

/**
 * @see https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#fcmoptions
 */
final class FcmOptions implements JsonSerializable
{
    /** @var array{
     *      analytics_label?: string
     *  }
     */
    private array $data;

    /**
     * @param array{
     *     analytics_label?: string
     * } $data
     */
    private function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function create(): self
    {
        return new self([]);
    }

    /**
     * @param array{
     *     analytics_label?: string
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    public function withAnalyticsLabel(string $label): self
    {
        $options = clone $this;
        $options->data['analytics_label'] = $label;

        return $options;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->data;
    }
}

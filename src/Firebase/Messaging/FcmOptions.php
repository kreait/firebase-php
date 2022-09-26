<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use JsonSerializable;

/**
 * @see https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#fcmoptions
 *
 * @phpstan-type FcmOptionsShape array{
 *     analytics_label?: string
 * }
 */
final class FcmOptions implements JsonSerializable
{
    /** @var FcmOptionsShape */
    private array $data;

    /** @param FcmOptionsShape $data */
    private function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function create(): self
    {
        return new self([]);
    }

    /**
     * @param FcmOptionsShape $data
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
     * @return FcmOptionsShape
     */
    public function jsonSerialize(): array
    {
        return $this->data;
    }
}

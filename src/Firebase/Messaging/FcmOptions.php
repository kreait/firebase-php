<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use JsonSerializable;

class FcmOptions implements JsonSerializable
{
    /** @var array */
    private $data;

    private function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function create(): self
    {
        return new self([]);
    }

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

    public function jsonSerialize()
    {
        return $this->data;
    }
}

<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use JsonSerializable;

final class AndroidConfig implements JsonSerializable
{
    /** @var array<string, mixed> */
    private $rawConfig;

    private function __construct()
    {
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

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return \array_filter($this->rawConfig, static function ($value) {
            return $value !== null;
        });
    }
}

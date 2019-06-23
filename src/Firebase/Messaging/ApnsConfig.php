<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

class ApnsConfig implements Config
{
    /**
     * @var array
     */
    private $rawConfig;

    private function __construct()
    {
    }

    public static function fromArray(array $data): self
    {
        $config = new self();
        $config->rawConfig = $data;

        return $config;
    }

    public function jsonSerialize()
    {
        return \array_filter($this->rawConfig, static function ($value) {
            return $value !== null;
        });
    }
}

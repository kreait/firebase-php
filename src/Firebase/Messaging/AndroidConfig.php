<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

class AndroidConfig implements Config
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
        return array_filter($this->rawConfig, function ($value) {
            return null !== $value;
        });
    }
}

<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;

trait ConfigTrait
{
    /**
     * @var array
     */
    private $data;

    public function setData(array $data): self
    {
        $config = clone $config;
        $config->data = $data;

        return $config;
    }

    /**
     * @return array
     */
    public function data(): array
    {
        return $this->data;
    }
}

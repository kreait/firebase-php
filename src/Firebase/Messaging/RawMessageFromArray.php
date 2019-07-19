<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

final class RawMessageFromArray implements Message
{
    /** @var array */
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function jsonSerialize()
    {
        return $this->data;
    }
}

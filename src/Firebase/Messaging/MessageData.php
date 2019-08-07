<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

final class MessageData implements \JsonSerializable
{
    /**
     * @var array
     */
    private $data = [];

    private function __construct()
    {
    }

    public static function fromArray(array $data): self
    {
        $messageData = new self();

        foreach ($data as $key => $value) {
            $messageData->data[(string) $key] = (string) $value;
        }

        return $messageData;
    }

    public function jsonSerialize()
    {
        return $this->data;
    }
}

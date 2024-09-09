<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Beste\Json;

/**
 * @phpstan-import-type MessageInputShape from Message
 * @phpstan-import-type MessageOutputShape from Message
 */
final class RawMessageFromArray implements Message
{
    /**
     * @param MessageInputShape $data
     */
    public function __construct(private readonly array $data)
    {
    }

    public function jsonSerialize(): array
    {
        return Json::decode(Json::encode($this->data), true);
    }
}

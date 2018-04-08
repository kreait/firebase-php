<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;

class MessageFactory
{
    public function fromArray(array $data): Message
    {
        if (\array_key_exists('message', $data) && \count($data) === 1) {
            $data = $data['message'];
        }

        if (array_key_exists('topic', $data)) {
            return MessageToTopic::fromArray($data);
        }

        if (array_key_exists('token', $data)) {
            return MessageToToken::fromArray($data);
        }

        if (array_key_exists('condition', $data)) {
            return ConditionalMessage::fromArray($data);
        }

        throw new InvalidArgumentException('Unsupported message type.');
    }
}

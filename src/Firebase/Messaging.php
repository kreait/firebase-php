<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Messaging\ApiClient;
use Kreait\Firebase\Messaging\Message;
use Kreait\Firebase\Messaging\MessageFactory;
use Kreait\Firebase\Util\JSON;

class Messaging
{
    /**
     * @var ApiClient
     */
    private $client;

    /**
     * @var MessageFactory
     */
    private $factory;

    public function __construct(ApiClient $client, MessageFactory $messageFactory)
    {
        $this->client = $client;
        $this->factory = $messageFactory;
    }

    /**
     * @param array|Message $message
     *
     * @return array
     */
    public function send($message): array
    {
        if (\is_array($message)) {
            $message = $this->factory->fromArray($message);
        }

        if (!($message instanceof Message)) {
            throw new InvalidArgumentException(
                'Unsupported message type. Use an array or a class implementing %s'.Message::class
            );
        }
        $response = $this->client->sendMessage($message);

        return JSON::decode((string) $response->getBody(), true);
    }
}

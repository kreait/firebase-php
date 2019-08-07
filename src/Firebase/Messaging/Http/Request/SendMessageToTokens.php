<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging\Http\Request;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Http\HasSubRequests;
use Kreait\Firebase\Http\Requests;
use Kreait\Firebase\Http\WrappedPsr7Request;
use Kreait\Firebase\Messaging\Message;
use Kreait\Firebase\Messaging\Messages;
use Kreait\Firebase\Messaging\RawMessageFromArray;
use Kreait\Firebase\Messaging\RegistrationTokens;
use Psr\Http\Message\RequestInterface;

final class SendMessageToTokens implements HasSubRequests, RequestInterface
{
    const MAX_AMOUNT_OF_TOKENS = 100;

    use WrappedPsr7Request;

    public function __construct(string $projectId, Message $message, RegistrationTokens $registrationTokens)
    {
        if ($registrationTokens->count() > self::MAX_AMOUNT_OF_TOKENS) {
            throw new InvalidArgumentException('A multicast message can be sent to a maximum amount of '.self::MAX_AMOUNT_OF_TOKENS.' tokens.');
        }

        $messageData = $message->jsonSerialize();
        unset($messageData['topic'], $messageData['condition']);

        $messages = [];

        foreach ($registrationTokens as $token) {
            $messageData['token'] = $token->value();

            $messages[] = new RawMessageFromArray($messageData);
        }

        $this->wrappedRequest = new SendMessages($projectId, new Messages(...$messages));
    }

    public function subRequests(): Requests
    {
        return $this->wrappedRequest->subRequests();
    }
}

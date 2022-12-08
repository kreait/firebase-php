<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging\Http\Request;

use Beste\Json;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\Messaging\InvalidArgument;
use Kreait\Firebase\Http\HasSubRequests;
use Kreait\Firebase\Http\WrappedPsr7Request;
use Kreait\Firebase\Messaging\Message;
use Kreait\Firebase\Messaging\Messages;
use Kreait\Firebase\Messaging\RawMessageFromArray;
use Kreait\Firebase\Messaging\RegistrationTokens;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 */
final class SendMessageToTokens implements HasSubRequests, RequestInterface
{
    use WrappedPsr7Request;

    public function __construct(string $projectId, Message $message, RegistrationTokens $registrationTokens, bool $validateOnly = false)
    {
        if ($registrationTokens->count() > Messaging::BATCH_MESSAGE_LIMIT) {
            throw new InvalidArgument('A multicast message can be sent to a maximum amount of '.Messaging::BATCH_MESSAGE_LIMIT.' tokens.');
        }

        $messageData = Json::decode(Json::encode($message), true);
        unset($messageData['topic'], $messageData['condition']);

        $messages = [];

        foreach ($registrationTokens as $token) {
            $messageData['token'] = $token->value();

            $messages[] = new RawMessageFromArray($messageData);
        }

        $this->wrappedRequest = new SendMessages($projectId, new Messages(...$messages), $validateOnly);
    }
}

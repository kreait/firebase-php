<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use GuzzleHttp\Promise;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Exception\Messaging\InvalidArgument;
use Kreait\Firebase\Exception\Messaging\InvalidMessage;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Messaging\ApiClient;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Message;
use Kreait\Firebase\Messaging\CloudMessageCollection;
use Kreait\Firebase\Messaging\MessageTarget;
use Kreait\Firebase\Messaging\MulticastSendReport;
use Kreait\Firebase\Messaging\RegistrationToken;
use Kreait\Firebase\Messaging\SendReport;
use Kreait\Firebase\Messaging\Topic;
use Kreait\Firebase\Messaging\TopicManagementApiClient;
use Kreait\Firebase\Util\JSON;
use Psr\Http\Message\ResponseInterface;

class Messaging
{
    const FCM_MAX_BATCH_SIZE = 100;

    /**
     * @var ApiClient
     */
    private $messagingApi;

    /**
     * @var TopicManagementApiClient
     */
    private $topicManagementApi;

    /**
     * @internal
     */
    public function __construct(ApiClient $messagingApiClient, TopicManagementApiClient $topicManagementApiClient)
    {
        $this->messagingApi = $messagingApiClient;
        $this->topicManagementApi = $topicManagementApiClient;
    }

    /**
     * @param array|CloudMessage|Message|mixed $message
     */
    public function send($message): array
    {
        $message = $this->checkMessage($message);
        if (($message instanceof CloudMessage) && !$message->hasTarget()) {
            throw new InvalidArgumentException('The given message has no target');
        }

        $response = $this->messagingApi->sendMessage($message);

        return JSON::decode((string) $response->getBody(), true);
    }

    /**
     * @param array $messages
     * @return array
     */
    public function sendAll(array $messages): MulticastSendReport
    {
        if (count($messages) > self::FCM_MAX_BATCH_SIZE) {
            throw new InvalidArgumentException(
                sprintf('messages list must not contain more than %d items', self::FCM_MAX_BATCH_SIZE)
            );
        }

        $collection = new CloudMessageCollection;
        foreach ($messages as $message) {
            $message = $this->checkMessage($message);
            if (($message instanceof CloudMessage) && !$message->hasTarget()) {
                throw new InvalidArgumentException('The given message has no target');
            }
            $collection->addMessage($message);
        }

        $reports = [];
        $sendResponse = $this->messagingApi->sendBatchRequest($collection);
        $requests = $collection->getIterator();
        while ($body = $sendResponse->getBody()) {
            $reports[] = $this->buildSendReport($requests->current()->getTarget(), \GuzzleHttp\Psr7\parse_response((string) $body));
            $requests->next();
        }

        return MulticastSendReport::withItems($reports);
    }

    /**
     * @param array|Message|mixed $message
     * @param string[]|RegistrationToken[] $deviceTokens
     */
    public function sendMulticast($message, array $deviceTokens): MulticastSendReport
    {
        $message = $this->checkMessage($message);
        if (!($message instanceof CloudMessage)) {
            $message = CloudMessage::fromArray($message->jsonSerialize());
        }

        $messages = [];

        foreach ($deviceTokens as $token) {
            $target = MessageTarget::with(MessageTarget::TOKEN, (string) $token);
            $messages[] = $message->withChangedTarget($target->type(), $target->value());
        }

        return $this->sendAll($messages);
    }

    /**
     * @param array|CloudMessage|Message|mixed $message
     *
     * @throws InvalidArgumentException
     * @throws InvalidMessage
     */
    public function validate($message): array
    {
        $message = $this->checkMessage($message);
        try {
            $response = $this->messagingApi->validateMessage($message);
        } catch (NotFound $e) {
            throw (new InvalidMessage($e->getMessage(), $e->getCode()))
                ->withResponse($e->response());
        }

        return JSON::decode((string) $response->getBody(), true);
    }

    /**
     * @param string|Topic $topic
     * @param RegistrationToken|RegistrationToken[]|string|string[] $registrationTokenOrTokens
     */
    public function subscribeToTopic($topic, $registrationTokenOrTokens): array
    {
        $topic = $topic instanceof Topic ? $topic : Topic::fromValue($topic);
        $tokens = $this->ensureArrayOfRegistrationTokens($registrationTokenOrTokens);

        $response = $this->topicManagementApi->subscribeToTopic($topic, $tokens);

        return JSON::decode((string) $response->getBody(), true);
    }

    /**
     * @param string|Topic $topic
     * @param RegistrationToken|RegistrationToken[]|string|string[] $registrationTokenOrTokens
     */
    public function unsubscribeFromTopic($topic, $registrationTokenOrTokens): array
    {
        $topic = $topic instanceof Topic ? $topic : Topic::fromValue($topic);
        $tokens = $this->ensureArrayOfRegistrationTokens($registrationTokenOrTokens);

        $response = $this->topicManagementApi->unsubscribeFromTopic($topic, $tokens);

        return JSON::decode((string) $response->getBody(), true);
    }

    private function ensureArrayOfRegistrationTokens($tokenOrTokens): array
    {
        if ($tokenOrTokens instanceof RegistrationToken) {
            return [$tokenOrTokens];
        }

        if (\is_string($tokenOrTokens)) {
            return [RegistrationToken::fromValue($tokenOrTokens)];
        }

        if (\is_array($tokenOrTokens)) {
            if (empty($tokenOrTokens)) {
                throw new InvalidArgument('Empty array of registration tokens.');
            }

            return \array_map(static function ($token) {
                return $token instanceof RegistrationToken ? $token : RegistrationToken::fromValue($token);
            }, $tokenOrTokens);
        }

        throw new InvalidArgument('Invalid registration tokens.');
    }


    private function checkMessage($message): Message
    {
        if (\is_array($message)) {
            $message = CloudMessage::fromArray($message);
        }
        if (!($message instanceof Message)) {
            throw new InvalidArgumentException(
                'Unsupported message type. Use an array or a class implementing %s' . Message::class
            );
        }
        return $message;
    }

    private function buildSendReport($target, ResponseInterface $response)
    {
        $isSuccess = $response->getStatusCode() === 200;
        if ($isSuccess) {
            $data = JSON::decode((string) $response->getBody(), true);
            return SendReport::success($target, $data);
        } else {
            return SendReport::failure($target, MessagingException::fromResponse($response));
        }
    }
}

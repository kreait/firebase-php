<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use GuzzleHttp\Promise;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Exception\Messaging\InvalidArgument;
use Kreait\Firebase\Exception\Messaging\InvalidMessage;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Messaging\ApiClient;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Message;
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
    /**
     * @var ApiClient
     */
    private $messagingApi;

    /**
     * @var TopicManagementApiClient
     */
    private $topicManagementApi;

    public function __construct(ApiClient $messagingApiClient, TopicManagementApiClient $topicManagementApiClient)
    {
        $this->messagingApi = $messagingApiClient;
        $this->topicManagementApi = $topicManagementApiClient;
    }

    /**
     * @param array|CloudMessage|Message|mixed $message
     *
     * @return array
     */
    public function send($message): array
    {
        if (\is_array($message)) {
            $message = CloudMessage::fromArray($message);
        }

        if (!($message instanceof Message)) {
            throw new InvalidArgumentException(
                'Unsupported message type. Use an array or a class implementing %s'.Message::class
            );
        }

        if (($message instanceof CloudMessage) && !$message->hasTarget()) {
            throw new InvalidArgumentException('The given message has no target');
        }

        $response = $this->messagingApi->sendMessage($message);

        return JSON::decode((string) $response->getBody(), true);
    }

    /**
     * @param array|Message|mixed $message
     * @param string[]|RegistrationToken[] $deviceTokens
     *
     * @return MulticastSendReport
     */
    public function sendMulticast($message, array $deviceTokens): MulticastSendReport
    {
        if (\is_array($message)) {
            $message = CloudMessage::fromArray($message);
        }

        if (!($message instanceof Message)) {
            throw new InvalidArgumentException(
                'Unsupported message type. Use an array or a class implementing %s'.Message::class
            );
        }

        if (!($message instanceof CloudMessage)) {
            $message = CloudMessage::fromArray($message->jsonSerialize());
        }

        $promises = [];

        foreach ($deviceTokens as $token) {
            $target = MessageTarget::with(MessageTarget::TOKEN, (string) $token);
            $message = $message->withChangedTarget($target->type(), $target->value());
            $promises[$target->value()] = $this->messagingApi->sendMessageAsync($message);
        }

        return Promise\settle($promises)
            ->then(static function (array $results) {
                $reports = [];

                foreach ($results as $tokenString => $result) {
                    $target = MessageTarget::with(MessageTarget::TOKEN, $tokenString);
                    if ($result['state'] === Promise\PromiseInterface::FULFILLED) {
                        /** @var ResponseInterface $response */
                        $response = $result['value'];
                        $data = JSON::decode((string) $response->getBody(), true);
                        $reports[] = SendReport::success($target, $data);
                    } else {
                        $reports[] = SendReport::failure($target, $result['reason']);
                    }
                }

                return MulticastSendReport::withItems($reports);
            })
            ->wait()
        ;
    }

    /**
     * @param array|CloudMessage|Message|mixed $message
     *
     * @throws InvalidArgumentException
     * @throws InvalidMessage
     *
     * @return array
     */
    public function validate($message): array
    {
        if (\is_array($message)) {
            $message = CloudMessage::fromArray($message);
        }

        if (!($message instanceof Message)) {
            throw new InvalidArgumentException(
                'Unsupported message type. Use an array or a class implementing %s'.Message::class
            );
        }

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
     *
     * @return array
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
     *
     * @return array
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

            return array_map(function ($token) {
                return $token instanceof RegistrationToken ? $token : RegistrationToken::fromValue($token);
            }, $tokenOrTokens);
        }

        throw new InvalidArgument('Invalid registration tokens.');
    }
}

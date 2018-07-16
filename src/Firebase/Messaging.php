<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Exception\Messaging\InvalidArgument;
use Kreait\Firebase\Exception\Messaging\InvalidMessage;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Messaging\ApiClient;
use Kreait\Firebase\Messaging\Message;
use Kreait\Firebase\Messaging\MessageFactory;
use Kreait\Firebase\Messaging\RegistrationToken;
use Kreait\Firebase\Messaging\Topic;
use Kreait\Firebase\Messaging\TopicManagementApiClient;
use Kreait\Firebase\Util\JSON;

class Messaging
{
    /**
     * @var ApiClient
     */
    private $messagingApi;

    /**
     * @var MessageFactory
     */
    private $factory;

    /**
     * @var TopicManagementApiClient
     */
    private $topicManagementApi;

    public function __construct(
        ApiClient $messagingApiClient,
        MessageFactory $messageFactory,
        TopicManagementApiClient $topicManagementApiClient
    ) {
        $this->messagingApi = $messagingApiClient;
        $this->factory = $messageFactory;
        $this->topicManagementApi = $topicManagementApiClient;
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
        $response = $this->messagingApi->sendMessage($message);

        return JSON::decode((string) $response->getBody(), true);
    }

    /**
     * @param array|Message $message
     *
     * @throws InvalidArgumentException
     * @throws InvalidMessage
     *
     * @return array
     */
    public function validate($message): array
    {
        if (\is_array($message)) {
            $message = $this->factory->fromArray($message);
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

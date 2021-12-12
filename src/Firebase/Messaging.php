<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use GuzzleHttp\Promise\Utils;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Exception\Messaging\InvalidArgument;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Http\ResponseWithSubResponses;
use Kreait\Firebase\Messaging\ApiClient;
use Kreait\Firebase\Messaging\AppInstance;
use Kreait\Firebase\Messaging\AppInstanceApiClient;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Http\Request\SendMessage;
use Kreait\Firebase\Messaging\Http\Request\SendMessages;
use Kreait\Firebase\Messaging\Http\Request\SendMessageToTokens;
use Kreait\Firebase\Messaging\Message;
use Kreait\Firebase\Messaging\Messages;
use Kreait\Firebase\Messaging\MulticastSendReport;
use Kreait\Firebase\Messaging\RegistrationToken;
use Kreait\Firebase\Messaging\RegistrationTokens;
use Kreait\Firebase\Messaging\Topic;
use Kreait\Firebase\Util\JSON;

class Messaging implements Contract\Messaging
{
    private string $projectId;

    private ApiClient $messagingApi;

    private AppInstanceApiClient $appInstanceApi;

    /**
     * @internal
     */
    public function __construct(string $projectId, ApiClient $messagingApiClient, AppInstanceApiClient $appInstanceApiClient)
    {
        $this->messagingApi = $messagingApiClient;
        $this->appInstanceApi = $appInstanceApiClient;
        $this->projectId = $projectId;
    }

    public function send($message, bool $validateOnly = false): array
    {
        $message = $this->makeMessage($message);

        $request = new SendMessage($this->projectId, $message, $validateOnly);

        try {
            $response = $this->messagingApi->send($request);
        } catch (NotFound $e) {
            $token = $message->jsonSerialize()['token'] ?? null;
            if ($token) {
                throw NotFound::becauseTokenNotFound($token);
            }

            throw $e;
        }

        return JSON::decode((string) $response->getBody(), true);
    }

    public function sendMulticast($message, $registrationTokens, bool $validateOnly = false): MulticastSendReport
    {
        $message = $this->makeMessage($message);
        $registrationTokens = $this->ensureNonEmptyRegistrationTokens($registrationTokens);

        $request = new SendMessageToTokens($this->projectId, $message, $registrationTokens, $validateOnly);
        /** @var ResponseWithSubResponses $response */
        $response = $this->messagingApi->send($request);

        return MulticastSendReport::fromRequestsAndResponses($request->subRequests(), $response->subResponses());
    }

    public function sendAll($messages, bool $validateOnly = false): MulticastSendReport
    {
        $ensuredMessages = [];

        foreach ($messages as $message) {
            $ensuredMessages[] = $this->makeMessage($message);
        }

        $request = new SendMessages($this->projectId, new Messages(...$ensuredMessages), $validateOnly);
        /** @var ResponseWithSubResponses $response */
        $response = $this->messagingApi->send($request);

        return MulticastSendReport::fromRequestsAndResponses($request->subRequests(), $response->subResponses());
    }

    public function validate($message): array
    {
        return $this->send($message, true);
    }

    public function validateRegistrationTokens($registrationTokenOrTokens): array
    {
        $registrationTokenOrTokens = $this->ensureNonEmptyRegistrationTokens($registrationTokenOrTokens);

        $report = $this->sendMulticast(CloudMessage::new(), $registrationTokenOrTokens, true);

        return [
            'valid' => $report->validTokens(),
            'unknown' => $report->unknownTokens(),
            'invalid' => $report->invalidTokens(),
        ];
    }

    public function subscribeToTopic($topic, $registrationTokenOrTokens): array
    {
        return $this->subscribeToTopics([$topic], $registrationTokenOrTokens);
    }

    public function subscribeToTopics(iterable $topics, $registrationTokenOrTokens): array
    {
        $topicObjects = [];

        foreach ($topics as $topic) {
            $topicObjects[] = $topic instanceof Topic ? $topic : Topic::fromValue($topic);
        }

        $tokens = $this->ensureNonEmptyRegistrationTokens($registrationTokenOrTokens);

        return $this->appInstanceApi->subscribeToTopics($topicObjects, $tokens);
    }

    public function unsubscribeFromTopic($topic, $registrationTokenOrTokens): array
    {
        return $this->unsubscribeFromTopics([$topic], $registrationTokenOrTokens);
    }

    public function unsubscribeFromTopics(array $topics, $registrationTokenOrTokens): array
    {
        $topics = \array_map(
            static fn ($topic) => $topic instanceof Topic ? $topic : Topic::fromValue($topic),
            $topics
        );

        $tokens = $this->ensureNonEmptyRegistrationTokens($registrationTokenOrTokens);

        return $this->appInstanceApi->unsubscribeFromTopics($topics, $tokens);
    }

    public function unsubscribeFromAllTopics($registrationTokenOrTokens): array
    {
        $tokens = $this->ensureNonEmptyRegistrationTokens($registrationTokenOrTokens);

        $promises = [];

        foreach ($tokens as $token) {
            $promises[$token->value()] = $this->appInstanceApi
                ->getAppInstanceAsync($token)
                ->then(function (AppInstance $appInstance) use ($token) {
                    $topics = [];
                    foreach ($appInstance->topicSubscriptions() as $subscription) {
                        $topics[] = $subscription->topic()->value();
                    }

                    return \array_keys($this->unsubscribeFromTopics($topics, $token));
                })
                ->otherwise(static fn (\Throwable $e) => $e->getMessage())
            ;
        }

        $responses = Utils::settle($promises)->wait();

        $result = [];

        foreach ($responses as $token => $response) {
            $result[(string) $token] = $response['value'];
        }

        return $result;
    }

    public function getAppInstance($registrationToken): AppInstance
    {
        $token = $registrationToken instanceof RegistrationToken
            ? $registrationToken
            : RegistrationToken::fromValue($registrationToken);

        try {
            return $this->appInstanceApi->getAppInstanceAsync($token)->wait();
        } catch (NotFound $e) {
            throw NotFound::becauseTokenNotFound($token->value());
        } catch (MessagingException $e) {
            // The token is invalid
            throw new InvalidArgument("The registration token '{$token}' is invalid or not available", $e->getCode(), $e);
        }
    }

    /**
     * @param Message|array<string, mixed> $message
     *
     * @throws InvalidArgumentException
     */
    private function makeMessage($message): Message
    {
        if ($message instanceof Message) {
            return $message;
        }

        return CloudMessage::fromArray($message);
    }

    /**
     * @param RegistrationTokens|RegistrationToken|RegistrationToken[]|string[]|string $value
     *
     * @throws InvalidArgument
     */
    private function ensureNonEmptyRegistrationTokens($value): RegistrationTokens
    {
        try {
            $tokens = RegistrationTokens::fromValue($value);
        } catch (InvalidArgumentException $e) {
            // We have to wrap the exception for BC reasons
            throw new InvalidArgument($e->getMessage());
        }

        if ($tokens->isEmpty()) {
            throw new InvalidArgument('Empty list of registration tokens.');
        }

        return $tokens;
    }
}

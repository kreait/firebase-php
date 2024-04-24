<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use Beste\Json;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\Utils;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Exception\Messaging\InvalidArgument;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Exception\MessagingApiExceptionConverter;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Messaging\ApiClient;
use Kreait\Firebase\Messaging\AppInstance;
use Kreait\Firebase\Messaging\AppInstanceApiClient;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Message;
use Kreait\Firebase\Messaging\MessageTarget;
use Kreait\Firebase\Messaging\MulticastSendReport;
use Kreait\Firebase\Messaging\Processor\SetApnsContentAvailableIfNeeded;
use Kreait\Firebase\Messaging\Processor\SetApnsPushTypeIfNeeded;
use Kreait\Firebase\Messaging\RegistrationToken;
use Kreait\Firebase\Messaging\RegistrationTokens;
use Kreait\Firebase\Messaging\SendReport;
use Kreait\Firebase\Messaging\Topic;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

use function array_key_exists;
use function array_keys;
use function array_map;

/**
 * @internal
 */
final class Messaging implements Contract\Messaging
{
    public function __construct(
        private readonly ApiClient $messagingApi,
        private readonly AppInstanceApiClient $appInstanceApi,
        private readonly MessagingApiExceptionConverter $exceptionConverter,
    ) {
    }

    public function send(Message|array $message, bool $validateOnly = false): array
    {
        $message = $this->makeMessage($message);

        if (!$this->messageHasTarget($message)) {
            throw new InvalidArgument('The given message is missing a target');
        }

        $reports = $this->sendAll([$message], $validateOnly)->getItems();
        $report = array_shift($reports);
        assert($report instanceof SendReport);

        if ($report->isSuccess()) {
            return $report->result() ?? [];
        }

        $error = $report->error();
        assert($error instanceof MessagingException);

        throw $error;
    }

    public function sendMulticast($message, $registrationTokens, bool $validateOnly = false): MulticastSendReport
    {
        $message = CloudMessage::fromArray(
            Json::decode(Json::encode($this->makeMessage($message)), true),
        );
        $registrationTokens = RegistrationTokens::fromValue($registrationTokens);

        $messages = [];

        foreach ($registrationTokens as $registrationToken) {
            $messages[] = $message->withChangedTarget(MessageTarget::TOKEN, $registrationToken->value());
        }

        return $this->sendAll($messages, $validateOnly);
    }

    public function sendAll($messages, bool $validateOnly = false): MulticastSendReport
    {
        $messages = $this->ensureMessages($messages);
        $requests = $this->createSendRequests($messages, $validateOnly);
        $sendReports = array_fill(0, count($messages), null);

        $config = [
            'fulfilled' => function (ResponseInterface $response, int $index) use ($messages, &$sendReports) {
                $message = $messages[$index];

                $json = Json::decode((string) $response->getBody(), true);

                $sendReports[$index] = SendReport::success($message->target(), $json, $message);
            },
            'rejected' => function (RequestException $reason, int $index) use ($messages, &$sendReports) {
                $message = $messages[$index];

                $error = $this->exceptionConverter->convertException($reason);

                $sendReports[$index] = SendReport::failure($message->target(), $error, $message);
            },
        ];

        $this->messagingApi->pool($requests(), $config)->wait();

        // $sendReports has the same size as $messages, and each key is set by the `fulfilled` and `rejected`
        // handlers above. The only way I could imagine a `null` value in the reports is when a request
        // didn't return a response at all. I don't think it's possible, so letting PHPStan know.
        assert(!in_array(null, $sendReports, true));

        return MulticastSendReport::withItems($sendReports);
    }

    public function validate($message): array
    {
        return $this->send($message, true);
    }

    public function validateRegistrationTokens($registrationTokenOrTokens): array
    {
        $tokens = RegistrationTokens::fromValue($registrationTokenOrTokens);

        $report = $this->sendMulticast(CloudMessage::new(), $tokens, true);

        return [
            'valid' => $report->validTokens(),
            'unknown' => $report->unknownTokens(),
            'invalid' => $report->invalidTokens(),
        ];
    }

    public function subscribeToTopic(string|Topic $topic, RegistrationTokens|RegistrationToken|array|string $registrationTokenOrTokens): array
    {
        return $this->subscribeToTopics([$topic], $registrationTokenOrTokens);
    }

    public function subscribeToTopics(iterable $topics, $registrationTokenOrTokens): array
    {
        $topicObjects = [];

        foreach ($topics as $topic) {
            $topicObjects[] = $topic instanceof Topic ? $topic : Topic::fromValue($topic);
        }

        $tokens = RegistrationTokens::fromValue($registrationTokenOrTokens);

        return $this->appInstanceApi->subscribeToTopics($topicObjects, $tokens);
    }

    public function unsubscribeFromTopic(string|Topic $topic, RegistrationTokens|RegistrationToken|array|string $registrationTokenOrTokens): array
    {
        return $this->unsubscribeFromTopics([$topic], $registrationTokenOrTokens);
    }

    public function unsubscribeFromTopics(array $topics, RegistrationTokens|RegistrationToken|array|string $registrationTokenOrTokens): array
    {
        $topics = array_map(
            static fn($topic) => $topic instanceof Topic ? $topic : Topic::fromValue($topic),
            $topics,
        );

        $tokens = RegistrationTokens::fromValue($registrationTokenOrTokens);

        return $this->appInstanceApi->unsubscribeFromTopics($topics, $tokens);
    }

    public function unsubscribeFromAllTopics($registrationTokenOrTokens): array
    {
        $tokens = RegistrationTokens::fromValue($registrationTokenOrTokens);

        $promises = [];

        foreach ($tokens as $token) {
            $promises[$token->value()] = $this->appInstanceApi
                ->getAppInstanceAsync($token)
                ->then(function (AppInstance $appInstance) use ($token) {
                    $topics = [];

                    foreach ($appInstance->topicSubscriptions() as $subscription) {
                        $topics[] = $subscription->topic()->value();
                    }

                    return array_keys($this->unsubscribeFromTopics($topics, $token));
                })
                ->otherwise(static fn(Throwable $e) => $e->getMessage())
            ;
        }

        $responses = Utils::settle($promises)->wait();

        $result = [];

        foreach ($responses as $token => $response) {
            $result[(string) $token] = $response['value'];
        }

        return $result;
    }

    public function getAppInstance(RegistrationToken|string $registrationToken): AppInstance
    {
        $token = $registrationToken instanceof RegistrationToken
            ? $registrationToken
            : RegistrationToken::fromValue($registrationToken);

        try {
            return $this->appInstanceApi->getAppInstance($token);
        } catch (NotFound $e) {
            throw NotFound::becauseTokenNotFound($token->value(), $e->errors());
        } catch (MessagingException $e) {
            // The token is invalid
            throw new InvalidArgument("The registration token '{$token}' is invalid or not available", $e->getCode(), $e);
        }
    }

    /**
     * @param iterable<Message|array<non-empty-string, mixed>> $messages
     *
     * @return list<CloudMessage>
     */
    private function ensureMessages(iterable $messages): array
    {
        $ensured = [];

        foreach ($messages as $message) {
            $ensured[] = $this->makeMessage($message);
        }

        return $ensured;
    }

    /**
     * @param Message|array<non-empty-string, mixed> $message
     *
     * @throws InvalidArgumentException
     */
    private function makeMessage(Message|array $message): CloudMessage
    {
        $message = $message instanceof Message ? $message : CloudMessage::fromArray($message);

        $message = (new SetApnsPushTypeIfNeeded())($message);
        $message = (new SetApnsContentAvailableIfNeeded())($message);

        return CloudMessage::fromArray(Json::decode(JSON::encode($message->jsonSerialize()), true));
    }

    /**
     * @param iterable<CloudMessage> $messages
     * @return callable(): list<RequestInterface>
     */
    private function createSendRequests(iterable $messages, bool $validateOnly): callable
    {
        return function () use ($messages, $validateOnly) {
            foreach ($messages as $message) {
                yield $this->messagingApi->createSendRequestForMessage($message, $validateOnly);
            }
        };
    }

    private function messageHasTarget(Message $message): bool
    {
        $check = Json::decode(Json::encode($message), true);

        return array_key_exists(MessageTarget::CONDITION, $check)
            || array_key_exists(MessageTarget::TOKEN, $check)
            || array_key_exists(MessageTarget::TOPIC, $check);
    }
}

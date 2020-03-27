<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Exception\Messaging\InvalidArgument;
use Kreait\Firebase\Exception\Messaging\InvalidMessage;
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
use Kreait\Firebase\Messaging\Http\Request\ValidateMessage;
use Kreait\Firebase\Messaging\Message;
use Kreait\Firebase\Messaging\Messages;
use Kreait\Firebase\Messaging\MulticastSendReport;
use Kreait\Firebase\Messaging\RegistrationToken;
use Kreait\Firebase\Messaging\RegistrationTokens;
use Kreait\Firebase\Messaging\Topic;
use Kreait\Firebase\Project\ProjectId;
use Kreait\Firebase\Util\JSON;

class Messaging
{
    /** @var string */
    private $projectId;

    /** @var ApiClient */
    private $messagingApi;

    /** @var AppInstanceApiClient */
    private $appInstanceApi;

    /**
     * @internal
     */
    public function __construct(ApiClient $messagingApiClient, AppInstanceApiClient $appInstanceApiClient, ProjectId $projectId = null)
    {
        $this->messagingApi = $messagingApiClient;
        $this->appInstanceApi = $appInstanceApiClient;

        if ($projectId) {
            $this->projectId = $projectId->value();
        } else {
            $this->projectId = $this->determineProjectIdFromMessagingApiClient($messagingApiClient);
        }
    }

    private function determineProjectIdFromMessagingApiClient(ApiClient $client): string
    {
        $baseUri = $client->getConfig('base_uri');
        $uriParts = \explode('/', (string) $baseUri);

        if (!($projectId = \array_pop($uriParts))) {
            throw new InvalidArgumentException("Project ID could not be determined from {$baseUri}");
        }

        return $projectId;
    }

    /**
     * @param array|Message|mixed $message
     *
     * @throws InvalidArgumentException
     * @throws MessagingException
     * @throws FirebaseException
     */
    public function send($message): array
    {
        $message = $this->makeMessage($message);

        $request = new SendMessage($this->projectId, $message);
        $response = $this->messagingApi->send($request);

        return JSON::decode((string) $response->getBody(), true);
    }

    /**
     * @param array|Message|mixed $message
     * @param RegistrationToken[]|string[]|RegistrationTokens $registrationTokens
     *
     * @throws InvalidArgumentException if the message is invalid
     * @throws MessagingException if the API request failed
     * @throws FirebaseException if something very unexpected happened (never :))
     */
    public function sendMulticast($message, $registrationTokens): MulticastSendReport
    {
        $message = $this->makeMessage($message);
        $registrationTokens = $this->ensureNonEmptyRegistrationTokens($registrationTokens);

        $request = new SendMessageToTokens($this->projectId, $message, $registrationTokens);
        /** @var ResponseWithSubResponses $response */
        $response = $this->messagingApi->send($request);

        return MulticastSendReport::fromRequestsAndResponses($request->subRequests(), $response->subResponses());
    }

    /**
     * @param array[]|Message[]|Messages $messages
     *
     * @throws InvalidArgumentException if the message is invalid
     * @throws MessagingException if the API request failed
     * @throws FirebaseException if something very unexpected happened (never :))
     */
    public function sendAll($messages): MulticastSendReport
    {
        $ensuredMessages = [];

        foreach ($messages as $message) {
            $ensuredMessages[] = $this->makeMessage($message);
        }

        $request = new SendMessages($this->projectId, new Messages(...$ensuredMessages));
        /** @var ResponseWithSubResponses $response */
        $response = $this->messagingApi->send($request);

        return MulticastSendReport::fromRequestsAndResponses($request->subRequests(), $response->subResponses());
    }

    /**
     * @param array|Message|mixed $message
     *
     * @throws InvalidArgumentException
     * @throws InvalidMessage
     * @throws MessagingException
     * @throws FirebaseException
     */
    public function validate($message): array
    {
        $message = $this->makeMessage($message);

        $request = new ValidateMessage($this->projectId, $message);
        try {
            $response = $this->messagingApi->send($request);
        } catch (NotFound $e) {
            $error = new InvalidMessage($e->getMessage(), $e->getCode(), $e->getPrevious());
            $error = $error->withErrors($e->errors());

            if ($response = $e->response()) {
                $error = $error->withResponse($response);
            }

            throw $error;
        }

        return JSON::decode((string) $response->getBody(), true);
    }

    /**
     * @param string|Topic $topic
     * @param mixed $registrationTokenOrTokens
     *
     * @throws MessagingException
     * @throws FirebaseException
     */
    public function subscribeToTopic($topic, $registrationTokenOrTokens): array
    {
        $topic = $topic instanceof Topic ? $topic : Topic::fromValue($topic);
        $tokens = $this->ensureNonEmptyRegistrationTokens($registrationTokenOrTokens);

        $response = $this->appInstanceApi->subscribeToTopic($topic, $tokens->asStrings());

        return JSON::decode((string) $response->getBody(), true);
    }

    /**
     * @param string|Topic $topic
     * @param mixed $registrationTokenOrTokens
     *
     * @throws MessagingException
     * @throws FirebaseException
     */
    public function unsubscribeFromTopic($topic, $registrationTokenOrTokens): array
    {
        $topic = $topic instanceof Topic ? $topic : Topic::fromValue($topic);
        $tokens = $this->ensureNonEmptyRegistrationTokens($registrationTokenOrTokens);

        $response = $this->appInstanceApi->unsubscribeFromTopic($topic, $tokens->asStrings());

        return JSON::decode((string) $response->getBody(), true);
    }

    /**
     * @see https://developers.google.com/instance-id/reference/server#results
     *
     * @param RegistrationToken|string $registrationToken
     *
     * @throws InvalidArgument if the registration token is invalid
     * @throws FirebaseException
     */
    public function getAppInstance($registrationToken): AppInstance
    {
        $token = $registrationToken instanceof RegistrationToken
            ? $registrationToken
            : RegistrationToken::fromValue($registrationToken);

        try {
            $response = $this->appInstanceApi->getAppInstance((string) $token);
        } catch (MessagingException $e) {
            // The token is invalid
            throw new InvalidArgument("The registration token '{$token}' is invalid");
        }

        $data = JSON::decode((string) $response->getBody(), true);

        return AppInstance::fromRawData($token, $data);
    }

    /**
     * @param mixed $message
     *
     * @throws InvalidArgumentException
     */
    private function makeMessage($message): Message
    {
        if ($message instanceof Message) {
            return $message;
        }

        if (!\is_array($message)) {
            throw new InvalidArgumentException(
                'Unsupported message type. Use an array or a class implementing %s'.Message::class
            );
        }

        return CloudMessage::fromArray($message);
    }

    /**
     * @param mixed $value
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

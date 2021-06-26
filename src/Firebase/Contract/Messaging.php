<?php

declare(strict_types=1);

namespace Kreait\Firebase\Contract;

use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Exception\Messaging\InvalidArgument;
use Kreait\Firebase\Exception\Messaging\InvalidMessage;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Messaging\AppInstance;
use Kreait\Firebase\Messaging\Message;
use Kreait\Firebase\Messaging\Messages;
use Kreait\Firebase\Messaging\MulticastSendReport;
use Kreait\Firebase\Messaging\RegistrationToken;
use Kreait\Firebase\Messaging\RegistrationTokens;
use Kreait\Firebase\Messaging\Topic;

interface Messaging
{
    /**
     * @param Message|array<string, mixed> $message
     *
     * @throws MessagingException
     * @throws FirebaseException
     * @throws InvalidArgumentException
     *
     * @return array<mixed>
     */
    public function send($message, bool $validateOnly = false): array;

    /**
     * @param Message|array<string, mixed> $message
     * @param RegistrationTokens|RegistrationToken|RegistrationToken[]|string[]|string $registrationTokens
     *
     * @throws InvalidArgumentException if the message is invalid
     * @throws MessagingException if the API request failed
     * @throws FirebaseException if something very unexpected happened (never :))
     */
    public function sendMulticast($message, $registrationTokens, bool $validateOnly = false): MulticastSendReport;

    /**
     * @param Message[]|Messages $messages
     *
     * @throws InvalidArgumentException if the message is invalid
     * @throws MessagingException if the API request failed
     * @throws FirebaseException if something very unexpected happened (never :))
     */
    public function sendAll($messages, bool $validateOnly = false): MulticastSendReport;

    /**
     * @param Message|array<string, mixed> $message
     *
     * @throws InvalidMessage
     * @throws MessagingException
     * @throws FirebaseException
     * @throws InvalidArgumentException
     *
     * @return array<mixed>
     */
    public function validate($message): array;

    /**
     * @param RegistrationTokens|RegistrationToken|RegistrationToken[]|string[]|string $registrationTokenOrTokens
     *
     * @throws MessagingException
     * @throws FirebaseException
     *
     * @return array<string, array<int, string>>
     */
    public function validateRegistrationTokens($registrationTokenOrTokens): array;

    /**
     * @param string|Topic $topic
     * @param RegistrationTokens|RegistrationToken|RegistrationToken[]|string[]|string $registrationTokenOrTokens
     *
     * @return array<string, array<string, string>>
     */
    public function subscribeToTopic($topic, $registrationTokenOrTokens): array;

    /**
     * @param iterable<string|Topic> $topics
     * @param RegistrationTokens|RegistrationToken|RegistrationToken[]|string[]|string $registrationTokenOrTokens
     *
     * @return array<string, array<string, string>>
     */
    public function subscribeToTopics(iterable $topics, $registrationTokenOrTokens): array;

    /**
     * @param string|Topic $topic
     * @param RegistrationTokens|RegistrationToken|RegistrationToken[]|string[]|string $registrationTokenOrTokens
     *
     * @return array<string, array<string, string>>
     */
    public function unsubscribeFromTopic($topic, $registrationTokenOrTokens): array;

    /**
     * @param array<string|Topic> $topics
     * @param RegistrationTokens|RegistrationToken|RegistrationToken[]|string[]|string $registrationTokenOrTokens
     *
     * @return array<string, array<string, string>>
     */
    public function unsubscribeFromTopics(array $topics, $registrationTokenOrTokens): array;

    /**
     * @param RegistrationTokens|RegistrationToken|RegistrationToken[]|string[]|string $registrationTokenOrTokens
     *
     * @return array<string, array<string, string>>
     */
    public function unsubscribeFromAllTopics($registrationTokenOrTokens): array;

    /**
     * @see https://developers.google.com/instance-id/reference/server#results
     *
     * @param RegistrationToken|string $registrationToken
     *
     * @throws InvalidArgument if the registration token is invalid
     * @throws FirebaseException
     */
    public function getAppInstance($registrationToken): AppInstance;
}

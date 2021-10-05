<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use DateTimeImmutable;
use JsonSerializable;

final class TopicSubscription implements JsonSerializable
{
    private Topic $topic;

    private RegistrationToken $registrationToken;

    private DateTimeImmutable $subscribedAt;

    public function __construct(Topic $topic, RegistrationToken $registrationToken, DateTimeImmutable $subscribedAt)
    {
        $this->topic = $topic;
        $this->registrationToken = $registrationToken;
        $this->subscribedAt = $subscribedAt;
    }

    public function topic(): Topic
    {
        return $this->topic;
    }

    public function registrationToken(): RegistrationToken
    {
        return $this->registrationToken;
    }

    public function subscribedAt(): DateTimeImmutable
    {
        return $this->subscribedAt;
    }

    /**
     * @return array<string, string>
     */
    public function jsonSerialize(): array
    {
        return [
            'topic' => $this->topic->value(),
            'registration_token' => $this->registrationToken->value(),
            'subscribed_at' => $this->subscribedAt->format(DATE_ATOM),
        ];
    }
}

<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use DateTimeImmutable;
use JsonSerializable;

use const DATE_ATOM;

final class TopicSubscription implements JsonSerializable
{
    public function __construct(
        private readonly Topic $topic,
        private readonly RegistrationToken $registrationToken,
        private readonly DateTimeImmutable $subscribedAt,
    ) {
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

    public function jsonSerialize(): array
    {
        return [
            'topic' => $this->topic->value(),
            'registration_token' => $this->registrationToken->value(),
            'subscribed_at' => $this->subscribedAt->format(DATE_ATOM),
        ];
    }
}

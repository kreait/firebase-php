<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use JsonSerializable;
use Kreait\Firebase\Util\DT;

/**
 * @see https://developers.google.com/instance-id/reference/server#results
 */
final class AppInstance implements JsonSerializable
{
    private RegistrationToken $registrationToken;

    /** @var array<string, mixed> */
    private array $rawData;

    private TopicSubscriptions $topicSubscriptions;

    /**
     * @param array<string, mixed> $rawData
     */
    private function __construct(RegistrationToken $registrationToken, TopicSubscriptions $topicSubscriptions, array $rawData)
    {
        $this->registrationToken = $registrationToken;
        $this->topicSubscriptions = $topicSubscriptions;
        $this->rawData = $rawData;
    }

    /**
     * @internal
     *
     * @param array<string, mixed> $rawData
     */
    public static function fromRawData(RegistrationToken $registrationToken, array $rawData): self
    {
        $subscriptions = [];

        foreach ($rawData['rel']['topics'] ?? [] as $topicName => $subscriptionInfo) {
            $topic = Topic::fromValue((string) $topicName);
            $addedAt = DT::toUTCDateTimeImmutable($subscriptionInfo['addDate'] ?? null);
            $subscriptions[] = new TopicSubscription($topic, $registrationToken, $addedAt);
        }

        return new self($registrationToken, new TopicSubscriptions(...$subscriptions), $rawData);
    }

    public function registrationToken(): RegistrationToken
    {
        return $this->registrationToken;
    }

    public function topicSubscriptions(): TopicSubscriptions
    {
        return $this->topicSubscriptions;
    }

    /**
     * @param Topic|string $topic
     */
    public function isSubscribedToTopic($topic): bool
    {
        $topic = $topic instanceof Topic ? $topic : Topic::fromValue($topic);

        return $this->topicSubscriptions
            ->filter(static fn (TopicSubscription $subscription) => $topic->value() === $subscription->topic()->value())
            ->count() > 0;
    }

    /**
     * @return array<string, mixed>
     */
    public function rawData(): array
    {
        return $this->rawData;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->rawData;
    }
}

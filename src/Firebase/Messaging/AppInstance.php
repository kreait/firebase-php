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
    /** @var RegistrationToken */
    private $registrationToken;

    /** @var array<string, mixed> */
    private $rawData = [];

    /** @var TopicSubscriptions */
    private $topicSubscriptions;

    private function __construct()
    {
        $this->topicSubscriptions = new TopicSubscriptions();
    }

    /**
     * @internal
     *
     * @param array<string, mixed> $rawData
     */
    public static function fromRawData(RegistrationToken $registrationToken, array $rawData): self
    {
        $info = new self();

        $info->registrationToken = $registrationToken;
        $info->rawData = $rawData;

        $subscriptions = [];

        foreach ($rawData['rel']['topics'] ?? [] as $topicName => $subscriptionInfo) {
            $topic = Topic::fromValue((string) $topicName);
            $addedAt = DT::toUTCDateTimeImmutable($subscriptionInfo['addDate'] ?? null);
            $subscriptions[] = new TopicSubscription($topic, $registrationToken, $addedAt);
        }

        $info->topicSubscriptions = new TopicSubscriptions(...$subscriptions);

        return $info;
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

        $filtered = $this->topicSubscriptions->filter(static function (TopicSubscription $subscription) use ($topic) {
            return $topic->value() === $subscription->topic()->value();
        });

        return $filtered->count() > 0;
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

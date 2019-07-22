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

    /** @var array */
    private $rawData = [];

    /** @var TopicSubscriptions */
    private $topicSubscriptions;

    private function __construct()
    {
        $this->topicSubscriptions = new TopicSubscriptions();
    }

    /**
     * @internal
     */
    public static function fromRawData(RegistrationToken $registrationToken, array $rawData): self
    {
        $info = new self();

        $info->registrationToken = $registrationToken;
        $info->rawData = $rawData;

        foreach ($rawData['rel']['topics'] ?? [] as $topicName => $subscriptionInfo) {
            $topic = Topic::fromValue($topicName);
            $addedAt = DT::toUTCDateTimeImmutable($subscriptionInfo['addDate'] ?? null);
            $subscription = new TopicSubscription($topic, $registrationToken, $addedAt);

            $info->topicSubscriptions->add($subscription);
        }

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

    public function isSubscribedToTopic($topic): bool
    {
        $topic = $topic instanceof Topic ? $topic : Topic::fromValue((string) $topic);

        $filtered = $this->topicSubscriptions->filter(static function (TopicSubscription $subscription) use ($topic) {
            return $topic->value() === $subscription->topic()->value();
        });

        return $filtered->count() > 0;
    }

    public function rawData(): array
    {
        return $this->rawData;
    }

    public function jsonSerialize()
    {
        return $this->rawData;
    }
}

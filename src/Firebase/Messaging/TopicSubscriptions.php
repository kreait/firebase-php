<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use ArrayIterator;
use Countable;
use IteratorAggregate;

final class TopicSubscriptions implements Countable, IteratorAggregate
{
    /** @var TopicSubscription[] */
    private $subscriptions = [];

    /**
     * @param TopicSubscription[] $subscriptions
     */
    public function __construct(array $subscriptions = [])
    {
        foreach ($subscriptions as $subscription) {
            if ($subscription instanceof TopicSubscription) {
                $this->add($subscription);
            }
        }
    }

    public function add(TopicSubscription $subscription)
    {
        $this->subscriptions[$subscription->topic()->value()] = $subscription;
    }

    public function filter(callable $filter): self
    {
        return new self(\array_filter($this->subscriptions, $filter));
    }

    /**
     * @return TopicSubscription[]
     */
    public function toArray(): array
    {
        return $this->subscriptions;
    }

    /**
     * @return ArrayIterator|TopicSubscription[]
     */
    public function getIterator()
    {
        return new ArrayIterator($this->subscriptions);
    }

    public function count()
    {
        return \count($this->subscriptions);
    }
}

<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Countable;
use IteratorAggregate;
use Traversable;

use function array_filter;
use function count;

/**
 * @implements IteratorAggregate<TopicSubscription>
 */
final class TopicSubscriptions implements Countable, IteratorAggregate
{
    /**
     * @var list<TopicSubscription>
     */
    private readonly array $subscriptions;

    public function __construct(TopicSubscription ...$subscriptions)
    {
        $this->subscriptions = array_values($subscriptions);
    }

    public function filter(callable $filter): self
    {
        return new self(...array_values(array_filter($this->subscriptions, $filter)));
    }

    /**
     * @return Traversable<TopicSubscription>
     */
    public function getIterator(): Traversable
    {
        yield from $this->subscriptions;
    }

    public function count(): int
    {
        return count($this->subscriptions);
    }
}

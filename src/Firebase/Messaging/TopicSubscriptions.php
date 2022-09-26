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
    /** @var TopicSubscription[] */
    private array $subscriptions;

    public function __construct(TopicSubscription ...$subscriptions)
    {
        $this->subscriptions = $subscriptions;
    }

    public function filter(callable $filter): self
    {
        return new self(...array_filter($this->subscriptions, $filter));
    }

    /**
     * @codeCoverageIgnore
     *
     * @return Traversable<TopicSubscription>|TopicSubscription[]
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

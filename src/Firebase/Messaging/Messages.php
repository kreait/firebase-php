<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Countable;
use IteratorAggregate;
use Traversable;

use function count;

/**
 * @implements IteratorAggregate<Message>
 */
final class Messages implements Countable, IteratorAggregate
{
    /**
     * @var Message[]
     */
    private readonly array $messages;

    public function __construct(Message ...$messages)
    {
        $this->messages = $messages;
    }

    /**
     * @return Traversable<Message>|Message[]
     */
    public function getIterator(): Traversable
    {
        yield from $this->messages;
    }

    public function count(): int
    {
        return count($this->messages);
    }
}

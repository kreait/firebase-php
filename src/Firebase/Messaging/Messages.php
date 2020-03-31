<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<Message>
 */
final class Messages implements Countable, IteratorAggregate
{
    /** @var Message[] */
    private $messages;

    public function __construct(Message ...$messages)
    {
        $this->messages = $messages;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return Traversable<Message>|Message[]
     */
    public function getIterator()
    {
        yield from $this->messages;
    }

    public function count(): int
    {
        return \count($this->messages);
    }
}

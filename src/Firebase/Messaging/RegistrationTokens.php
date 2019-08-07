<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Countable;
use Generator;
use IteratorAggregate;

final class RegistrationTokens implements Countable, IteratorAggregate
{
    /** @var RegistrationToken[] */
    private $tokens;

    public function __construct(RegistrationToken ...$tokens)
    {
        $this->tokens = $tokens;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return Generator|RegistrationToken[]
     */
    public function getIterator()
    {
        yield from $this->tokens;
    }

    public function count()
    {
        return \count($this->tokens);
    }
}

<?php

declare(strict_types=1);

namespace Kreait\Firebase\Http;

use Generator;
use IteratorAggregate;
use Psr\Http\Message\RequestInterface;

final class Requests implements IteratorAggregate
{
    /** @var RequestInterface[] */
    private $requests;

    public function __construct(RequestInterface ...$requests)
    {
        $this->requests = $requests;
    }

    /**
     * @return RequestInterface|null
     */
    public function findBy(callable $callable)
    {
        $results = \array_filter($this->requests, $callable);

        return \array_shift($results) ?: null;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return Generator|RequestInterface[]
     */
    public function getIterator()
    {
        yield from $this->requests;
    }
}

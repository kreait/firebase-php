<?php

declare(strict_types=1);

namespace Kreait\Firebase\Http;

use IteratorAggregate;
use Psr\Http\Message\RequestInterface;
use Traversable;

/**
 * @implements IteratorAggregate<RequestInterface>
 */
final class Requests implements IteratorAggregate
{
    /** @var RequestInterface[] */
    private $requests;

    public function __construct(RequestInterface ...$requests)
    {
        $this->requests = $requests;
    }

    public function findBy(callable $callable): ?RequestInterface
    {
        $results = \array_filter($this->requests, $callable);

        return \array_shift($results) ?: null;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return Traversable<RequestInterface>|RequestInterface[]
     */
    public function getIterator()
    {
        yield from $this->requests;
    }
}

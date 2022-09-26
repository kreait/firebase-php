<?php

declare(strict_types=1);

namespace Kreait\Firebase\Http;

use IteratorAggregate;
use Psr\Http\Message\ResponseInterface;
use Traversable;

/**
 * @internal
 * @implements IteratorAggregate<ResponseInterface>
 */
final class Responses implements IteratorAggregate
{
    /** @var ResponseInterface[] */
    private array $responses;

    public function __construct(ResponseInterface ...$responses)
    {
        $this->responses = $responses;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return Traversable<ResponseInterface>|ResponseInterface[]
     */
    public function getIterator(): Traversable
    {
        yield from $this->responses;
    }
}

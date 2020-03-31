<?php

declare(strict_types=1);

namespace Kreait\Firebase\Http;

use IteratorAggregate;
use Psr\Http\Message\ResponseInterface;
use Traversable;

/**
 * @implements IteratorAggregate<ResponseInterface>
 */
final class Responses implements IteratorAggregate
{
    /** @var ResponseInterface[] */
    private $responses;

    public function __construct(ResponseInterface ...$responses)
    {
        $this->responses = $responses;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return Traversable<ResponseInterface>|ResponseInterface[]
     */
    public function getIterator()
    {
        yield from $this->responses;
    }
}

<?php

declare(strict_types=1);

namespace Kreait\Firebase\Http;

use Generator;
use IteratorAggregate;
use Psr\Http\Message\ResponseInterface;

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
     * @return Generator|ResponseInterface[]
     */
    public function getIterator()
    {
        yield from $this->responses;
    }
}

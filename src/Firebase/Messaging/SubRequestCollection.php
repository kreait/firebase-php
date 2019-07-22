<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Psr\Http\Message\RequestInterface;
use Traversable;

/**
 * Class SubRequestCollection
 * @package Kreait\Firebase\Messaging
 */
class SubRequestCollection implements \IteratorAggregate
{
    /**
     * @var array
     */
    private $requests;

    /**
     * SubRequestCollection constructor.
     */
    public function __construct()
    {
        $this->requests = [];
    }

    /**
     * Retrieve an external iterator
     * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->requests);
    }

    /**
     * Append request.
     * @param RequestInterface $request
     */
    public function addRequest(RequestInterface $request)
    {
        $this->requests[] = $request;
    }
}

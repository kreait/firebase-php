<?php

namespace Firebase\Database\Query\Filter;

use Firebase\Database\Query\Filter;
use Firebase\Database\Query\ModifierTrait;
use Firebase\Exception\InvalidArgumentException;
use Psr\Http\Message\UriInterface;

final class LimitToLast implements Filter
{
    use ModifierTrait;

    private $limit;

    public function __construct(int $limit)
    {
        if ($limit < 1) {
            throw new InvalidArgumentException('Limit must be 1 or greater');
        }

        $this->limit = $limit;
    }

    public function modifyUri(UriInterface $uri): UriInterface
    {
        return $this->appendQueryParam($uri, 'limitToLast', $this->limit);
    }
}

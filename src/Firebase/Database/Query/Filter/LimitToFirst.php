<?php

declare(strict_types=1);

namespace Kreait\Firebase\Database\Query\Filter;

use Kreait\Firebase\Database\Query\Filter;
use Kreait\Firebase\Database\Query\ModifierTrait;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Psr\Http\Message\UriInterface;

final class LimitToFirst implements Filter
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
        return $this->appendQueryParam($uri, 'limitToFirst', $this->limit);
    }
}

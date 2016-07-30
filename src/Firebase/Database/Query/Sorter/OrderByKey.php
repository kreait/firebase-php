<?php

namespace Firebase\Database\Query\Sorter;

use Firebase\Database\Query\ModifierTrait;
use Firebase\Database\Query\Sorter;
use Psr\Http\Message\UriInterface;

final class OrderByKey implements Sorter
{
    use ModifierTrait;

    private $sort;

    public function __construct(int $sort = SORT_ASC)
    {
        $this->sort = $sort;
    }

    public function modifyUri(UriInterface $uri): UriInterface
    {
        return $this->appendQueryParam($uri, 'orderBy', '"$key"');
    }

    public function modifyValue($value)
    {
        if (!is_array($value)) {
            return $value;
        }

        if ($this->sort === SORT_ASC) {
            ksort($value);
        } elseif ($this->sort === SORT_DESC) {
            krsort($value);
        }

        return $value;
    }
}

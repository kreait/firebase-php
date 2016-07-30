<?php

namespace Firebase\Database\Query\Sorter;

use Firebase\Database\Query\ModifierTrait;
use Firebase\Database\Query\Sorter;
use Psr\Http\Message\UriInterface;

final class OrderByValue implements Sorter
{
    use ModifierTrait;

    private $sort;

    public function __construct(int $sort = SORT_ASC)
    {
        $this->sort = $sort;
    }

    public function modifyUri(UriInterface $uri): UriInterface
    {
        return $this->appendQueryParam($uri, 'orderBy', '"$value"');
    }

    public function modifyValue($value)
    {
        if (!is_array($value)) {
            return $value;
        }

        if ($this->sort === SORT_ASC) {
            asort($value);
        } elseif ($this->sort === SORT_DESC) {
            arsort($value);
        }

        return $value;
    }
}

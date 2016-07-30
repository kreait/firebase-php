<?php

namespace Firebase\Database\Query\Sorter;

use Firebase\Database\Query\ModifierTrait;
use Firebase\Database\Query\Sorter;
use Psr\Http\Message\UriInterface;

final class OrderByChild implements Sorter
{
    use ModifierTrait;

    private $childKey;
    private $sort;

    public function __construct(string $childKey, int $sort = SORT_ASC)
    {
        $this->childKey = $childKey;
        $this->sort = $sort;
    }

    public function modifyUri(UriInterface $uri): UriInterface
    {
        return $this->appendQueryParam($uri, 'orderBy', sprintf('"%s"', $this->childKey));
    }

    public function modifyValue($value)
    {
        if (!is_array($value)) {
            return $value;
        }

        $childKey = $this->childKey;

        if ($this->sort === SORT_ASC) {
            uasort($value, function ($a, $b) use ($childKey) {
                return ($a[$childKey] ?? null) <=> $b[$childKey] ?? null;
            });
        } elseif ($this->sort === SORT_DESC) {
            uasort($value, function ($a, $b) use ($childKey) {
                return ($b[$childKey] ?? null) <=> $a[$childKey] ?? null;
            });
        }

        return $value;
    }
}

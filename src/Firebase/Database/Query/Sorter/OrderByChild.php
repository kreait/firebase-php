<?php

namespace Firebase\Database\Query\Sorter;

use Firebase\Database\Query\ModifierTrait;
use Firebase\Database\Query\Sorter;
use Psr\Http\Message\UriInterface;

final class OrderByChild implements Sorter
{
    use ModifierTrait;

    private $childKey;

    public function __construct(string $childKey)
    {
        $this->childKey = $childKey;
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

        uasort($value, function ($a, $b) use ($childKey) {
            return ($a[$childKey] ?? null) <=> $b[$childKey] ?? null;
        });

        return $value;
    }
}

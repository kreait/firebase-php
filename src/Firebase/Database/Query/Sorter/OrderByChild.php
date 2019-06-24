<?php

declare(strict_types=1);

namespace Kreait\Firebase\Database\Query\Sorter;

use function JmesPath\search;
use Kreait\Firebase\Database\Query\ModifierTrait;
use Kreait\Firebase\Database\Query\Sorter;
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
        return $this->appendQueryParam($uri, 'orderBy', \sprintf('"%s"', $this->childKey));
    }

    public function modifyValue($value)
    {
        if (!\is_array($value)) {
            return $value;
        }

        $expression = \str_replace('/', '.', $this->childKey);

        \uasort($value, static function ($a, $b) use ($expression) {
            return search($expression, $a) <=> search($expression, $b);
        });

        return $value;
    }
}

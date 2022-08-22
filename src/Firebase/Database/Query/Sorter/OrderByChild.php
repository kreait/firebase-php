<?php

declare(strict_types=1);

namespace Kreait\Firebase\Database\Query\Sorter;

use Kreait\Firebase\Database\Query\ModifierTrait;
use Kreait\Firebase\Database\Query\Sorter;
use Psr\Http\Message\UriInterface;

use function is_array;
use function JmesPath\search;
use function sprintf;
use function str_replace;
use function uasort;

final class OrderByChild implements Sorter
{
    use ModifierTrait;
    private string $childKey;

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

        $expression = str_replace('/', '.', $this->childKey);

        uasort($value, static fn ($a, $b) => search($expression, $a) <=> search($expression, $b));

        return $value;
    }
}

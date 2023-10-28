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

/**
 * @internal
 */
final class OrderByChild implements Sorter
{
    use ModifierTrait;

    public function __construct(private readonly string $childKey)
    {
    }

    public function modifyUri(UriInterface $uri): UriInterface
    {
        return $this->appendQueryParam($uri, 'orderBy', sprintf('"%s"', $this->childKey));
    }

    public function modifyValue($value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        $expression = str_replace('/', '.', $this->childKey);

        uasort($value, static fn($a, $b) => search($expression, $a) <=> search($expression, $b));

        return $value;
    }
}

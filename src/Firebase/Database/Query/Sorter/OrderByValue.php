<?php

declare(strict_types=1);

namespace Kreait\Firebase\Database\Query\Sorter;

use Kreait\Firebase\Database\Query\ModifierTrait;
use Kreait\Firebase\Database\Query\Sorter;
use Psr\Http\Message\UriInterface;

use function asort;
use function is_array;

/**
 * @internal
 */
final class OrderByValue implements Sorter
{
    use ModifierTrait;

    public function modifyUri(UriInterface $uri): UriInterface
    {
        return $this->appendQueryParam($uri, 'orderBy', '"$value"');
    }

    public function modifyValue(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        asort($value);

        return $value;
    }
}

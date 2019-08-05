<?php

declare(strict_types=1);

namespace Kreait\Firebase\Database\Query;

use function GuzzleHttp\Psr7\build_query;
use function GuzzleHttp\Psr7\parse_query;
use Psr\Http\Message\UriInterface;

/**
 * @codeCoverageIgnore
 */
trait ModifierTrait
{
    protected function appendQueryParam(UriInterface $uri, string $key, $value): UriInterface
    {
        $queryParams = \array_merge(parse_query($uri->getQuery()), [$key => $value]);

        $queryString = build_query($queryParams);

        return $uri->withQuery($queryString);
    }

    public function modifyValue($value)
    {
        return $value;
    }
}

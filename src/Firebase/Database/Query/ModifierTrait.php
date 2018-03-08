<?php

namespace Kreait\Firebase\Database\Query;

use Psr\Http\Message\UriInterface;
use function GuzzleHttp\Psr7\build_query;
use function GuzzleHttp\Psr7\parse_query;

trait ModifierTrait
{
    protected function appendQueryParam(UriInterface $uri, string $key, $value): UriInterface
    {
        $queryParams = array_merge(parse_query($uri->getQuery()), [$key => $value]);

        $queryString = build_query($queryParams);

        return $uri->withQuery($queryString);
    }

    public function modifyValue($value)
    {
        return $value;
    }
}

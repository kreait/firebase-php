<?php

namespace Firebase\Database\Query;

use GuzzleHttp\Psr7;
use Psr\Http\Message\UriInterface;

trait ModifierTrait
{
    protected function appendQueryParam(UriInterface $uri, string $key, $value): UriInterface
    {
        $queryParams = array_merge(Psr7\parse_query($uri->getQuery()), [$key => $value]);

        $queryString = Psr7\build_query($queryParams);

        return $uri->withQuery($queryString);
    }

    public function modifyValue($value)
    {
        return $value;
    }
}

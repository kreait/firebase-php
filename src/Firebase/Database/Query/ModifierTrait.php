<?php

declare(strict_types=1);

namespace Kreait\Firebase\Database\Query;

use GuzzleHttp\Psr7\Query;
use Psr\Http\Message\UriInterface;

use function array_merge;

/**
 * @internal
 */
trait ModifierTrait
{
    public function modifyValue(mixed $value): mixed
    {
        return $value;
    }

    protected function appendQueryParam(UriInterface $uri, string $key, mixed $value): UriInterface
    {
        $queryParams = array_merge(Query::parse($uri->getQuery()), [$key => $value]);

        $queryString = Query::build($queryParams);

        return $uri->withQuery($queryString);
    }
}

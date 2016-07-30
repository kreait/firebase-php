<?php

namespace Firebase\Database\Query;

use Psr\Http\Message\UriInterface;

interface Modifier
{
    public function modifyUri(UriInterface $uri): UriInterface;

    public function modifyValue($value);
}

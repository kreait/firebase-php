<?php

declare(strict_types=1);

namespace Kreait\Firebase\Database\Query;

use Psr\Http\Message\UriInterface;

/**
 * @internal
 */
interface Modifier
{
    /**
     * Modifies the given URI and returns it.
     */
    public function modifyUri(UriInterface $uri): UriInterface;

    /**
     * Modifies the given value and returns it.
     */
    public function modifyValue(mixed $value): mixed;
}

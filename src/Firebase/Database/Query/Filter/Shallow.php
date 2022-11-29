<?php

declare(strict_types=1);

namespace Kreait\Firebase\Database\Query\Filter;

use Kreait\Firebase\Database\Query\Filter;
use Kreait\Firebase\Database\Query\ModifierTrait;
use Psr\Http\Message\UriInterface;

/**
 * @internal
 */
final class Shallow implements Filter
{
    use ModifierTrait;

    public function modifyUri(UriInterface $uri): UriInterface
    {
        return $this->appendQueryParam($uri, 'shallow', 'true');
    }
}

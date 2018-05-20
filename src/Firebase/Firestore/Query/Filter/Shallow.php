<?php

namespace Kreait\Firebase\Firestore\Query\Filter;

use Kreait\Firebase\Database\Query\Filter;
use Kreait\Firebase\Database\Query\ModifierTrait;
use Psr\Http\Message\UriInterface;

final class Shallow implements Filter
{
    use ModifierTrait;

    public function modifyUri(UriInterface $uri): UriInterface
    {
        return $this->appendQueryParam($uri, 'shallow', 'true');
    }
}

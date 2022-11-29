<?php

declare(strict_types=1);

namespace Kreait\Firebase\Database\Query\Filter;

use Beste\Json;
use Kreait\Firebase\Database\Query\Filter;
use Kreait\Firebase\Database\Query\ModifierTrait;
use Psr\Http\Message\UriInterface;

/**
 * @internal
 */
final class EqualTo implements Filter
{
    use ModifierTrait;

    public function __construct(private readonly bool|float|int|string $value)
    {
    }

    public function modifyUri(UriInterface $uri): UriInterface
    {
        return $this->appendQueryParam($uri, 'equalTo', Json::encode($this->value));
    }
}

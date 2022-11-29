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
final class StartAfter implements Filter
{
    use ModifierTrait;

    public function __construct(private readonly int|float|string|bool $value)
    {
    }

    public function modifyUri(UriInterface $uri): UriInterface
    {
        return $this->appendQueryParam($uri, 'startAfter', Json::encode($this->value));
    }
}

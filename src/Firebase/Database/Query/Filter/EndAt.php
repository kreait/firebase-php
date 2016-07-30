<?php

namespace Firebase\Database\Query\Filter;

use Firebase\Database\Query\Filter;
use Firebase\Database\Query\ModifierTrait;
use Firebase\Exception\InvalidArgumentException;
use Psr\Http\Message\UriInterface;

final class EndAt implements Filter
{
    use ModifierTrait;

    private $value;

    public function __construct($value)
    {
        if (!is_scalar($value)) {
            throw new InvalidArgumentException('Only scalar values are allowed for "endAt" queries.');
        }

        $this->value = $value;
    }

    public function modifyUri(UriInterface $uri): UriInterface
    {
        return $this->appendQueryParam($uri, 'endAt', $this->value);
    }
}

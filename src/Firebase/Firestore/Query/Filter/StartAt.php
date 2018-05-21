<?php

namespace Kreait\Firebase\Firestore\Query\Filter;

use Kreait\Firebase\Database\Query\Filter;
use Kreait\Firebase\Database\Query\ModifierTrait;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Util\JSON;
use Psr\Http\Message\UriInterface;

final class StartAt implements Filter
{
    use ModifierTrait;

    private $value;

    public function __construct($value)
    {
        if (!is_scalar($value)) {
            throw new InvalidArgumentException('Only scalar values are allowed for "startAt" queries.');
        }

        $this->value = $value;
    }

    public function modifyUri(UriInterface $uri): UriInterface
    {
        return $this->appendQueryParam($uri, 'startAt', JSON::encode($this->value));
    }
}

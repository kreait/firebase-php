<?php

declare(strict_types=1);

namespace Kreait\Firebase\Database\Query\Filter;

use Beste\Json;
use Kreait\Firebase\Database\Query\Filter;
use Kreait\Firebase\Database\Query\ModifierTrait;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Psr\Http\Message\UriInterface;

final class EqualTo implements Filter
{
    use ModifierTrait;

    /** @var bool|float|int|string */
    private $value;

    /**
     * @param scalar $value
     */
    public function __construct($value)
    {
        if (!\is_scalar($value)) {
            throw new InvalidArgumentException('Only scalar values are allowed for "equalTo" queries.');
        }

        $this->value = $value;
    }

    public function modifyUri(UriInterface $uri): UriInterface
    {
        return $this->appendQueryParam($uri, 'equalTo', Json::encode($this->value));
    }
}

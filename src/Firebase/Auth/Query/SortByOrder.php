<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth\Query;

use JsonSerializable;
use Kreait\Firebase\Exception\InvalidArgumentException;

/**
 * @see https://cloud.google.com/identity-platform/docs/reference/rest/v1/Order
 */
final class SortByOrder implements JsonSerializable
{
    private string $value;

    public const SORT_BY_ORDER_ASC = 'ASC';
    public const SORT_BY_ORDER_DESC = 'DESC';


    public const VALID_SORT_BY_ORDERS = [
        self::SORT_BY_ORDER_ASC,
        self::SORT_BY_ORDER_DESC
    ];

    public function __construct(string $value)
    {
        if (!\in_array($value, self::VALID_SORT_BY_ORDERS)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid sort by order: %s. Valid values are: %s',
                $value,
                \implode(',', self::VALID_SORT_BY_ORDERS)));
        }

        $this->value = $value;
    }

    public function __toString()
    {
        return $this->value;
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}

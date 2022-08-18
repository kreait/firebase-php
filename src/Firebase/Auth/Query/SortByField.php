<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth\Query;

use JsonSerializable;
use Kreait\Firebase\Exception\InvalidArgumentException;

/**
 * @see https://cloud.google.com/identity-platform/docs/reference/rest/v1/SortByField
 */
final class SortByField implements JsonSerializable
{
    private string $value;

    public const SORT_BY_USER_ID = 'USER_ID';
    public const SORT_BY_NAME = 'NAME';
    public const SORT_BY_CREATED_AT = 'CREATED_AT';
    public const SORT_BY_LAST_LOGIN_AT = 'LAST_LOGIN_AT';
    public const SORT_BY_USER_EMAIL = 'USER_EMAIL';

    public const VALID_SORT_BY_VALUES = [
        self::SORT_BY_USER_ID,
        self::SORT_BY_NAME,
        self::SORT_BY_CREATED_AT,
        self::SORT_BY_LAST_LOGIN_AT,
        self::SORT_BY_USER_EMAIL,
    ];

    public function __construct(string $value)
    {
        if (!\in_array($value, self::VALID_SORT_BY_VALUES)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid sort by value: %s. Valid values are: %s',
                $value,
                \implode(',', self::VALID_SORT_BY_VALUES)));
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

<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use Beste\Json;
use Kreait\Firebase\Exception\InvalidArgumentException;


/**
 * @see https://cloud.google.com/identity-platform/docs/reference/rest/v1/projects.accounts/query#request-body
 */
class UserQuery implements \JsonSerializable
{
    private int $limit;
    private int $offset;
    private string $sortBy;
    private string $order ;

    public const FIELD_USER_ID = 'USER_ID';
    public const FIELD_NAME = 'NAME';
    public const FIELD_CREATED_AT = 'CREATED_AT';
    public const FIELD_LAST_LOGIN_AT = 'LAST_LOGIN_AT';
    public const FIELD_USER_EMAIL = 'USER_EMAIL';

    public const VALID_SORT_BY_VALUES = [
        self::FIELD_USER_ID,
        self::FIELD_NAME,
        self::FIELD_CREATED_AT,
        self::FIELD_LAST_LOGIN_AT,
        self::FIELD_USER_EMAIL,
    ];

    public const SORT_BY_ORDER_ASC = 'ASC';
    public const SORT_BY_ORDER_DESC = 'DESC';

    public const VALID_SORT_BY_ORDERS = [
        self::SORT_BY_ORDER_ASC,
        self::SORT_BY_ORDER_DESC
    ];

    public function __construct()
    {
        $this->limit = 500;
        $this->offset = 0;
        $this->sortBy = self::FIELD_USER_ID;
        $this->order = self::SORT_BY_ORDER_ASC;
    }

    /**
     * @param value-of<self::VALID_SORT_BY_VALUES> $sortedBy
     *
     * @throws InvalidArgumentException
     *
     */
    public function sortedBy(string $sortedBy): self
    {
        if (!\in_array($sortedBy, self::VALID_SORT_BY_VALUES)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid sort by value: %s. Valid values are: %s',
                $sortedBy,
                \implode(',', self::VALID_SORT_BY_VALUES)));
        }
        $query = clone $this;
        $query->sortBy = $sortedBy;

        return $query;
    }

    /**
     * @param value-of<self::VALID_SORT_BY_ORDERS> $orderBy
     *
     * @throws InvalidArgumentException
     *
     */
    public function orderBy(string $orderBy): self
    {
        if (!\in_array($orderBy, self::VALID_SORT_BY_ORDERS)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid sort by order: %s. Valid values are: %s',
                $orderBy,
                \implode(',', self::VALID_SORT_BY_ORDERS)));
        }
        $query = clone $this;
        $query->order = $orderBy;

        return $query;
    }

    public static function all(): self
    {
        return new self();
    }

    public function inAscendingOrder(): self
    {
        return $this->orderBy(self::SORT_BY_ORDER_ASC);
    }

    public function inDescendingOrder(): self
    {
        return $this->orderBy(self::SORT_BY_ORDER_DESC);
    }

    public function withOffset(int $offset): self
    {
        $query = clone $this;
        $query->offset = $offset;

        return $query;
    }

    public function limitedTo(int $limit): self
    {
        $query = clone $this;
        $query->limit = $limit;

        return $query;
    }

    /**
     * @param array{
     *     limit?: positive-int,
     *     offset?: positive-int,
     *     sortBy?: value-of<self::VALID_SORT_BY_VALUES>,
     *     order?: value-of<self::VALID_SORT_BY_ORDERS>
     * } $data
     */
    public static function fromArray(array $data): self
    {
        $new = new self();

        if ($limit = ($data['limit'] ?? null)) {
            $new = $new->limitedTo($limit);
        }

        if ($offset = ($data['offset'] ?? null)) {
            $new = $new->withOffset($offset);
        }

        if ($sortBy = ($data['sortBy'] ?? null)) {
            $new = $new->sortedBy($sortBy);
        }

        if ($order = ($data['order'] ?? null)) {
            $new = $new->orderBy($order);
        }

        return $new;
    }

    public function jsonSerialize()
    {
        $data = [
            'limit' => $this->limit,
            'offset' => $this->offset,
            'sortBy' => $this->sortBy,
            'order' => $this->order,
        ];

        $data = Json::decode(Json::encode($data), true);


        return \array_filter(
            $data,
            static fn ($value) => $value !== null && $value !== []
        );
    }
}

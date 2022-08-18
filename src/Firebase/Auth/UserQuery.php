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
    private ?int $limit = null;
    private ?int $offset = null;
    private ?string $sortBy = null;
    private ?string $order = null;

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


    /**
     * @param value-of<self::VALID_SORT_BY_VALUES> $sortedBy
     */
    public function sortedBy(string $sortedBy): self
    {
        $query = clone $this;
        $query->sortBy = $sortedBy;

        return $query;
    }

    /**
     * @param value-of<self::VALID_SORT_BY_ORDERS> $orderBy
     */
    public function orderBy(string $orderBy): self
    {
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
     *     limit?: int<0, 500>,
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

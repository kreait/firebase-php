<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use Beste\Json;
use Kreait\Firebase\Auth\Query\SortByField;
use Kreait\Firebase\Auth\Query\SortByOrder;
use Kreait\Firebase\Exception\InvalidArgumentException;


/**
 * @see https://cloud.google.com/identity-platform/docs/reference/rest/v1/projects.accounts/query#request-body
 */
class UserQuery implements \JsonSerializable
{
    private int $limit;
    private int $offset;
    private SortByField $sortBy;
    private SortByOrder $order ;

    public function __construct()
    {
        $this->limit = 500;
        $this->offset = 0;
        $this->sortBy = new SortByField(SortByField::SORT_BY_USER_ID);
        $this->order = new SortByOrder(SortByOrder::SORT_BY_ORDER_ASC);
    }

    /**
     * @param SortByField|string $sortedBy
     *
     * @throws InvalidArgumentException
     *
     */
    public function sortedBy($sortedBy): self
    {
        $query = clone $this;
        $query->sortBy =  $sortedBy instanceof SortByField ? $sortedBy : new SortByField($sortedBy);

        return $query;
    }

    /**
     * @param SortByOrder|string $orderBy
     *
     * @throws InvalidArgumentException
     *
     */
    public function orderBy($orderBy): self
    {
        $query = clone $this;
        $query->order =  $orderBy instanceof SortByOrder ? $orderBy : new SortByOrder($orderBy);

        return $query;
    }

    public static function all(): self
    {
        return new self();
    }

    public function inAscendingOrder(): self
    {
        return $this->orderBy(SortByOrder::SORT_BY_ORDER_ASC);
    }

    public function inDescendingOrder(): self
    {
        return $this->orderBy(SortByOrder::SORT_BY_ORDER_DESC);
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
     * @param array {
     *     limit?: positive-int,
     *     offset?: positive-int,
     *     sortBy?: SortByField|non-empty-string,
     *     order?: SortByOrder|non-empty-string
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

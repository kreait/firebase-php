<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use JsonSerializable;

use function array_filter;

/**
 * @see https://cloud.google.com/identity-platform/docs/reference/rest/v1/projects.accounts/query#request-body
 *
 * @phpstan-type UserQueryShape array{
 *     sortBy?: self::FIELD_*,
 *     order?: self::ORDER_*,
 *     offset?: int<0, max>,
 *     limit?: int<1, self::MAX_LIMIT>,
 *     filter?: array<self::FILTER_*, non-empty-string>
 * }
 */
class UserQuery implements JsonSerializable
{
    public const FIELD_CREATED_AT = 'CREATED_AT';
    public const FIELD_LAST_LOGIN_AT = 'LAST_LOGIN_AT';
    public const FIELD_NAME = 'NAME';
    public const FIELD_USER_EMAIL = 'USER_EMAIL';
    public const FIELD_USER_ID = 'USER_ID';
    public const FILTER_EMAIL = 'email';
    public const FILTER_PHONE_NUMBER = 'phoneNumber';
    public const FILTER_USER_ID = 'userId';
    public const ORDER_ASC = 'ASC';
    public const ORDER_DESC = 'DESC';
    public const MAX_LIMIT = 500;

    /** @var int<1, self::MAX_LIMIT>|null */
    private ?int $limit = null;

    /** @var int<0, max>|null */
    private ?int $offset = null;

    /** @var self::FIELD_*|null */
    private ?string $sortBy = null;

    /** @var self::ORDER_*|null */
    private ?string $order = null;

    /** @var array<self::FILTER_*, non-empty-string>|null */
    private ?array $filter = null;

    private function __construct()
    {
    }

    public static function all(): self
    {
        return new self();
    }

    /**
     * @param UserQueryShape $data
     */
    public static function fromArray(array $data): self
    {
        $query = new self();

        $query->sortBy = $data['sortBy'] ?? null;
        $query->order = $data['order'] ?? null;
        $query->offset = $data['offset'] ?? null;
        $query->limit = $data['limit'] ?? null;
        $query->filter = $data['filter'] ?? null;

        return $query;
    }

    /**
     * @param self::FIELD_* $sortedBy
     */
    public function sortedBy(string $sortedBy): self
    {
        $query = clone $this;
        $query->sortBy = $sortedBy;

        return $query;
    }

    public function inAscendingOrder(): self
    {
        return $this->withOrder(self::ORDER_ASC);
    }

    public function inDescendingOrder(): self
    {
        return $this->withOrder(self::ORDER_DESC);
    }

    /**
     * @param int<0, max> $offset
     */
    public function withOffset(int $offset): self
    {
        $query = clone $this;
        $query->offset = $offset;

        return $query;
    }

    /**
     * @param int<1, self::MAX_LIMIT> $limit
     */
    public function withLimit(int $limit): self
    {
        $query = clone $this;
        $query->limit = $limit;

        return $query;
    }

    /**
     * @param self::FILTER_* $field
     * @param non-empty-string $value
     */
    public function withFilter(string $field, string $value): self
    {
        $query = clone $this;
        $query->filter = [$field => $value];

        return $query;
    }

    public function jsonSerialize(): array
    {
        $data = array_filter([
            'returnUserInfo' => true,
            'limit' => $this->limit,
            'offset' => $this->offset,
            'sortBy' => $this->sortBy,
            'order' => $this->order,
        ]);

        if ($this->filter !== null) {
            $data['expression'] = $this->filter;
        }

        return $data;
    }

    /**
     * @param self::ORDER_* $direction
     */
    private function withOrder(string $direction): self
    {
        $query = clone $this;
        $query->order = $direction;

        return $query;
    }
}

<?php

/*
 * This file is part of the firebase-php package.
 *
 * (c) Jérôme Gamez <jerome@kreait.com>
 * (c) kreait GmbH <info@kreait.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Kreait\Firebase;

/**
 * @link https://www.firebase.com/docs/rest/guide/retrieving-data.html#section-rest-queries Querying data.
 */
class Query
{
    const LIMIT_TO_FIRST = 'limitToFirst';
    const LIMIT_TO_LAST = 'limitToLast';

    /**
     * A key to order by.
     *
     * @var string
     */
    private $orderBy;

    /**
     * A limitation.
     *
     * @var array
     */
    private $limitTo;

    /**
     * The starting point for the query.
     *
     * @var int|string
     */
    private $startAt;

    /**
     * The end point for the query.
     *
     * @var int|string
     */
    private $endAt;

    /**
     * Whether the query is shallow or not.
     *
     * @var bool
     */
    private $shallow;

    /**
     * Order results by the given child key.
     *
     * @param string $childKey The key to order by.
     *
     * @return $this
     */
    public function orderByChildKey($childKey)
    {
        $this->orderBy = sprintf('"%s"', $childKey);

        return $this;
    }

    /**
     * @return $this
     */
    public function orderByKey()
    {
        $this->orderBy = '"$key"';

        return $this;
    }

    /**
     * Order results by priority.
     *
     * @return $this
     */
    public function orderByPriority()
    {
        $this->orderBy = '"$priority"';

        return $this;
    }

    /**
     * Limit the result to the first x items.
     *
     * @param int $limit The number.
     *
     * @return $this
     */
    public function limitToFirst($limit)
    {
        $this->limitTo = [self::LIMIT_TO_FIRST, $limit];

        return $this;
    }

    /**
     * Limit the result to the last x items.
     *
     * @param int $limit The number.
     *
     * @return $this
     */
    public function limitToLast($limit)
    {
        $this->limitTo = [self::LIMIT_TO_LAST, $limit];

        return $this;
    }

    /**
     * Set starting point for the Query.
     *
     * @param int|string $startAt
     *
     * @return $this
     */
    public function startAt($startAt)
    {
        $this->startAt = $startAt;

        return $this;
    }

    /**
     * Set end point for the Query.
     *
     * @param int|string $endAt
     *
     * @return $this
     */
    public function endAt($endAt)
    {
        $this->endAt = $endAt;

        return $this;
    }

    /**
     * Mark query as shallow.
     *
     * @param bool $shallow
     *
     * @return $this
     */
    public function shallow($shallow)
    {
        $this->shallow = $shallow;

        return $this;
    }

    /**
     * Returns an array representation of this query.
     *
     * @return array
     */
    public function toArray()
    {
        if ($this->shallow) {
            return ['shallow' => 'true'];
        }

        $result = [];

        // An orderBy must be set for the other parameters to work
        $result['orderBy'] = $this->orderBy ?: '"$key"';

        if ($this->limitTo) {
            $result[$this->limitTo[0]] = $this->limitTo[1];
        }

        if ($this->startAt) {
            $result['startAt'] = sprintf('"%s"', $this->startAt);
        }

        if ($this->endAt) {
            $result['endAt'] = sprintf('"%s"', $this->endAt);
        }

        return $result;
    }

    public function __toString()
    {
        return http_build_query($this->toArray(), null, '&', PHP_QUERY_RFC3986);
    }
}

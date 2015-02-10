<?php
/**
 * This file is part of the firebase-php package.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Kreait\Firebase;

/**
 * @link https://www.firebase.com/docs/rest/guide/retrieving-data.html#section-rest-queries Querying data.
 */
class Query
{
    const LIMIT_TO_FIRST = 'limitToFirst';
    const LIMIT_TO_LAST  = 'limitToLast';

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
     * If the result should be shallow or not.
     *
     * @var bool
     */
    private $shallow;

    /**
     * Order results by the given child key.
     *
     * @param  string $childKey The key to order by.
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
     * @param  int   $limit The number.
     * @return $this
     */
    public function limitToFirst($limit)
    {
        $this->limitTo = [self::LIMIT_TO_FIRST, $limit];

        return $this;
    }

    /**
     * Limit the result to the first x items.
     *
     * @param  int   $limit The number.
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
     * @param  int|string $start
     * @return $this
     */
    public function startAt($start)
    {
        $this->startAt = sprintf('"%s"', $start);

        return $this;
    }

    /**
     * Set end point for the Query.
     *
     * @param  int|string $end
     * @return $this
     */
    public function endAt($end)
    {
        $this->endAt = sprintf('"%s"', $end);

        return $this;
    }

    /**
     * Let the result be shallow.
     *
     * @param bool $shallow
     *
     * @return $this
     */
    public function shallow($shallow = true)
    {
        $this->shallow = $shallow;

        return $this;
    }

    public function __toString()
    {
        $params = [];

        if ($this->orderBy) {
            $params['orderBy'] = $this->orderBy;
        }

        if ($this->limitTo) {
            $params[$this->limitTo[0]] = $this->limitTo[1];
        }

        if ($this->startAt) {
            $params['startAt'] = $this->startAt;
        }

        if ($this->endAt) {
            $params['endAt'] = $this->endAt;
        }

        if ($this->shallow) {
            $params['shallow'] = "true";
        }

        return http_build_query($params, null, '&', PHP_QUERY_RFC3986);
    }
}

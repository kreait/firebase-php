<?php

declare(strict_types=1);

namespace Kreait\Firebase\Database;

use Kreait\Firebase\Database\Query\Filter;
use Kreait\Firebase\Database\Query\Sorter;
use Kreait\Firebase\Exception\ApiException;
use Kreait\Firebase\Exception\QueryException;
use Psr\Http\Message\UriInterface;

/**
 * A Query sorts and filters the data at a database location so only a subset of the child data is included.
 * This can be used to order a collection of data by some attribute (e.g. height of dinosaurs) as well as
 * to restrict a large list of items (e.g. chat messages) down to a number suitable for synchronizing
 * to the client. Queries are created by chaining together one or more of the filter methods
 * defined here.
 *
 * Just as with a Reference, you can receive data from a Query by using the
 * {@see getSnapshot()} or {@see getValue()} method. You will only receive
 * Snapshots for the subset of the data that matches your query.
 *
 * @see https://firebase.google.com/docs/reference/js/firebase.database.Query
 */
class Query
{
    /**
     * @var Reference
     */
    private $reference;

    /**
     * @var ApiClient
     */
    private $apiClient;

    /**
     * @var Filter[]
     */
    private $filters;

    /**
     * @var Sorter
     */
    private $sorter;

    /**
     * @internal
     */
    public function __construct(Reference $reference, ApiClient $apiClient)
    {
        $this->reference = $reference;
        $this->apiClient = $apiClient;
        $this->filters = [];
    }

    /**
     * Returns a Reference to the Query's location.
     *
     * @see https://firebase.google.com/docs/reference/js/firebase.database.Query#ref
     */
    public function getReference(): Reference
    {
        return $this->reference;
    }

    /**
     * Returns a data snapshot of the current location.
     *
     * @throws QueryException if an error occurred
     */
    public function getSnapshot(): Snapshot
    {
        try {
            $value = $this->apiClient->get($this->getUri());
        } catch (ApiException $e) {
            throw QueryException::fromApiException($e, $this);
        }

        if ($this->sorter) {
            $value = $this->sorter->modifyValue($value);
        }

        foreach ($this->filters as $filter) {
            $value = $filter->modifyValue($value);
        }

        return new Snapshot($this->reference, $value);
    }

    /**
     * Convenience method for {@see getSnapshot()}->getValue().
     *
     * @throws QueryException if an error occurred
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->getSnapshot()->getValue();
    }

    /**
     * Creates a Query with the specified ending point.
     *
     * The ending point is inclusive, so children with exactly
     * the specified value will be included in the query.
     *
     * @see https://firebase.google.com/docs/reference/js/firebase.database.Query#endAt
     *
     * @param int|float|string|bool $value
     *
     * @return Query
     */
    public function endAt($value): self
    {
        return $this->withAddedFilter(new Filter\EndAt($value));
    }

    /**
     * Creates a Query which includes children which match the specified value.
     *
     * @see https://firebase.google.com/docs/reference/js/firebase.database.Query#equalTo
     *
     * @param int|float|string|bool $value
     *
     * @return Query
     */
    public function equalTo($value): self
    {
        return $this->withAddedFilter(new Filter\EqualTo($value));
    }

    /**
     * Creates a Query with the specified starting point.
     *
     * The starting point is inclusive, so children with exactly the specified value will be included in the query.
     *
     * @see https://firebase.google.com/docs/reference/js/firebase.database.Query#startAt
     *
     * @param int|float|string|bool $value
     *
     * @return Query
     */
    public function startAt($value): self
    {
        return $this->withAddedFilter(new Filter\StartAt($value));
    }

    /**
     * Generates a new Query limited to the first specific number of children.
     *
     * @see https://firebase.google.com/docs/reference/js/firebase.database.Query#limitToFirst
     *
     * @return Query
     */
    public function limitToFirst(int $limit): self
    {
        return $this->withAddedFilter(new Filter\LimitToFirst($limit));
    }

    /**
     * Generates a new Query object limited to the last specific number of children.
     *
     * @see https://firebase.google.com/docs/reference/js/firebase.database.Query#limitToLast
     *
     * @return Query
     */
    public function limitToLast(int $limit): self
    {
        return $this->withAddedFilter(new Filter\LimitToLast($limit));
    }

    /**
     * Generates a new Query object ordered by the specified child key.
     *
     * Queries can only order by one key at a time. Calling orderBy*() multiple times on
     * the same query is an error.
     *
     * @see https://firebase.google.com/docs/reference/js/firebase.database.Query#orderByChild
     *
     * @throws QueryException if the query is already ordered
     *
     * @return Query
     */
    public function orderByChild(string $childKey): self
    {
        return $this->withSorter(new Sorter\OrderByChild($childKey));
    }

    /**
     * Generates a new Query object ordered by key.
     *
     * Sorts the results of a query by their ascending key value.
     *
     * Queries can only order by one key at a time. Calling orderBy*() multiple times on
     * the same query is an error.
     *
     * @see https://firebase.google.com/docs/reference/js/firebase.database.Query#orderByKey
     *
     * @throws QueryException if the query is already ordered
     *
     * @return Query
     */
    public function orderByKey(): self
    {
        return $this->withSorter(new Sorter\OrderByKey());
    }

    /**
     * Generates a new Query object ordered by child values.
     *
     * If the children of a query are all scalar values (numbers or strings), you can order the results
     * by their (ascending) values.
     *
     * Queries can only order by one key at a time. Calling orderBy*() multiple times on
     * the same query is an error.
     *
     * @see https://firebase.google.com/docs/reference/js/firebase.database.Query#orderByValue
     *
     * @throws QueryException if the query is already ordered
     *
     * @return Query
     */
    public function orderByValue(): self
    {
        return $this->withSorter(new Sorter\OrderByValue());
    }

    /**
     * This is an advanced feature, designed to help you work with large datasets without needing to download
     * everything. Set this to true to limit the depth of the data returned at a location. If the data at
     * the location is a JSON primitive (string, number or boolean), its value will simply be returned.
     *
     * If the data snapshot at the location is a JSON object, the values for each key will be
     * truncated to true.
     *
     * @see https://firebase.google.com/docs/reference/rest/database/#section-param-shallow
     *
     * @return Query
     */
    public function shallow(): self
    {
        return $this->withAddedFilter(new Filter\Shallow());
    }

    /**
     * Returns the absolute URL for this location.
     *
     * This method returns a URL that is ready to be put into a browser, curl command, or a
     * {@see Database::getReferenceFromUrl()} call. Since all of those expect the URL
     * to be url-encoded, toString() returns an encoded URL.
     *
     * Append '.json' to the URL when typed into a browser to download JSON formatted data.
     * If the location is secured (not publicly readable) you will get a permission-denied error.
     */
    public function getUri(): UriInterface
    {
        $uri = $this->reference->getUri();

        if ($this->sorter) {
            $uri = $this->sorter->modifyUri($uri);
        }

        foreach ($this->filters as $filter) {
            $uri = $filter->modifyUri($uri);
        }

        return $uri;
    }

    /**
     * Returns the absolute URL for this location.
     *
     * @see getUri()
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getUri();
    }

    private function withAddedFilter(Filter $filter): self
    {
        $query = clone $this;
        $query->filters[] = $filter;

        return $query;
    }

    private function withSorter(Sorter $sorter): self
    {
        if ($this->sorter) {
            throw new QueryException($this, 'This query is already ordered.');
        }

        $query = clone $this;
        $query->sorter = $sorter;

        return $query;
    }
}

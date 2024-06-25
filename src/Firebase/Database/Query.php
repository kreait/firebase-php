<?php

declare(strict_types=1);

namespace Kreait\Firebase\Database;

use Kreait\Firebase\Database\Query\Filter;
use Kreait\Firebase\Database\Query\Filter\EndAt;
use Kreait\Firebase\Database\Query\Filter\EndBefore;
use Kreait\Firebase\Database\Query\Filter\EqualTo;
use Kreait\Firebase\Database\Query\Filter\LimitToFirst;
use Kreait\Firebase\Database\Query\Filter\LimitToLast;
use Kreait\Firebase\Database\Query\Filter\Shallow;
use Kreait\Firebase\Database\Query\Filter\StartAfter;
use Kreait\Firebase\Database\Query\Filter\StartAt;
use Kreait\Firebase\Database\Query\Sorter;
use Kreait\Firebase\Database\Query\Sorter\OrderByChild;
use Kreait\Firebase\Database\Query\Sorter\OrderByKey;
use Kreait\Firebase\Database\Query\Sorter\OrderByValue;
use Kreait\Firebase\Exception\Database\DatabaseNotFound;
use Kreait\Firebase\Exception\Database\UnsupportedQuery;
use Kreait\Firebase\Exception\DatabaseException;
use Psr\Http\Message\UriInterface;
use Stringable;

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
 */
class Query implements Stringable
{
    /**
     * @var Filter[]
     */
    private array $filters = [];
    private ?Sorter $sorter = null;

    /**
     * @internal
     */
    public function __construct(private readonly Reference $reference, private readonly ApiClient $apiClient)
    {
    }

    /**
     * Returns the absolute URL for this location.
     *
     * @see getUri()
     */
    public function __toString(): string
    {
        return (string) $this->getUri();
    }

    /**
     * Returns a Reference to the Query's location.
     */
    public function getReference(): Reference
    {
        return $this->reference;
    }

    /**
     * Returns a data snapshot of the current location.
     *
     * @throws UnsupportedQuery if an error occurred
     */
    public function getSnapshot(): Snapshot
    {
        $uri = $this->getUri();

        $pathAndQuery = $uri->getPath().'?'.$uri->getQuery();

        try {
            $value = $this->apiClient->get($pathAndQuery);
        } catch (DatabaseNotFound $e) {
            throw $e;
        } catch (DatabaseException $e) {
            throw new UnsupportedQuery($this, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }

        if ($this->sorter !== null) {
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
     * @throws UnsupportedQuery if an error occurred
     */
    public function getValue(): mixed
    {
        return $this->getSnapshot()->getValue();
    }

    /**
     * Creates a Query with the specified ending point.
     *
     * The ending point is inclusive, so children with exactly
     * the specified value will be included in the query.
     *
     * @param scalar $value
     */
    public function endAt($value): self
    {
        return $this->withAddedFilter(new EndAt($value));
    }

    /**
     * Creates a Query with the specified ending point (exclusive).
     *
     * @param scalar $value
     */
    public function endBefore($value): self
    {
        return $this->withAddedFilter(new EndBefore($value));
    }

    /**
     * Creates a Query which includes children which match the specified value.
     *
     * @param scalar $value
     */
    public function equalTo($value): self
    {
        return $this->withAddedFilter(new EqualTo($value));
    }

    /**
     * Creates a Query with the specified starting point (inclusive).
     *
     * @param scalar $value
     */
    public function startAt($value): self
    {
        return $this->withAddedFilter(new StartAt($value));
    }

    /**
     * Creates a Query with the specified starting point (exclusive).
     *
     * @param scalar $value
     */
    public function startAfter($value): self
    {
        return $this->withAddedFilter(new StartAfter($value));
    }

    /**
     * Generates a new Query limited to the first specific number of children.
     */
    public function limitToFirst(int $limit): self
    {
        return $this->withAddedFilter(new LimitToFirst($limit));
    }

    /**
     * Generates a new Query object limited to the last specific number of children.
     */
    public function limitToLast(int $limit): self
    {
        return $this->withAddedFilter(new LimitToLast($limit));
    }

    /**
     * Generates a new Query object ordered by the specified child key.
     *
     * Queries can only order by one key at a time. Calling orderBy*() multiple times on
     * the same query is an error.
     *
     * @throws UnsupportedQuery if the query is already ordered
     */
    public function orderByChild(string $childKey): self
    {
        return $this->withSorter(new OrderByChild($childKey));
    }

    /**
     * Generates a new Query object ordered by key.
     *
     * Sorts the results of a query by their ascending key value.
     *
     * Queries can only order by one key at a time. Calling orderBy*() multiple times on
     * the same query is an error.
     *
     * @throws UnsupportedQuery if the query is already ordered
     */
    public function orderByKey(): self
    {
        return $this->withSorter(new OrderByKey());
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
     * @throws UnsupportedQuery if the query is already ordered
     */
    public function orderByValue(): self
    {
        return $this->withSorter(new OrderByValue());
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
     */
    public function shallow(): self
    {
        return $this->withAddedFilter(new Shallow());
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

        if ($this->sorter !== null) {
            $uri = $this->sorter->modifyUri($uri);
        }

        foreach ($this->filters as $filter) {
            $uri = $filter->modifyUri($uri);
        }

        return $uri;
    }

    private function withAddedFilter(Filter $filter): self
    {
        $query = clone $this;
        $query->filters[] = $filter;

        return $query;
    }

    private function withSorter(Sorter $sorter): self
    {
        if ($this->sorter !== null) {
            throw new UnsupportedQuery($this, 'This query is already ordered.');
        }

        $query = clone $this;
        $query->sorter = $sorter;

        return $query;
    }
}

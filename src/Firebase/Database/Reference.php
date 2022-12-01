<?php

declare(strict_types=1);

namespace Kreait\Firebase\Database;

use Kreait\Firebase\Database\Reference\Validator;
use Kreait\Firebase\Exception\DatabaseException;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Exception\OutOfRangeException;
use Psr\Http\Message\UriInterface;
use Stringable;

use function array_fill_keys;
use function array_keys;
use function array_map;
use function basename;
use function dirname;
use function is_array;
use function ltrim;
use function sprintf;
use function trim;

/**
 * A Reference represents a specific location in your database and can be used
 * for reading or writing data to that database location.
 *
 * @see https://firebase.google.com/docs/reference/js/firebase.database.Reference
 */
class Reference implements Stringable
{
    private readonly UriInterface $uri;

    /**
     * @internal
     *
     * @throws InvalidArgumentException if the reference URI is invalid
     */
    public function __construct(
        UriInterface $uri,
        private readonly ApiClient $apiClient,
        private readonly UrlBuilder $urlBuilder,
        private readonly Validator $validator = new Validator(),
    ) {
        $this->validator->validateUri($uri);

        $this->uri = $uri;
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
     * The last part of the current path.
     *
     * For example, "ada" is the key for https://sample-app.firebaseio.com/users/ada.
     *
     * The key of the root Reference is null.
     *
     * @see https://firebase.google.com/docs/reference/js/firebase.database.Reference#key
     */
    public function getKey(): ?string
    {
        $key = basename($this->getPath());

        return $key !== '' ? $key : null;
    }

    /**
     * Returns the full path to a reference.
     */
    public function getPath(): string
    {
        return trim($this->uri->getPath(), '/');
    }

    /**
     * The parent location of a Reference.
     *
     * @see https://firebase.google.com/docs/reference/js/firebase.database.Reference#parent
     *
     * @throws OutOfRangeException if requested for the root Reference
     */
    public function getParent(): self
    {
        $parentPath = dirname($this->getPath());

        if ($parentPath === '.') {
            throw new OutOfRangeException('Cannot get parent of root reference');
        }

        return new self(
            $this->uri->withPath('/'.ltrim($parentPath, '/')),
            $this->apiClient,
            $this->urlBuilder,
            $this->validator,
        );
    }

    /**
     * The root location of a Reference.
     *
     * @see https://firebase.google.com/docs/reference/js/firebase.database.Reference#root
     */
    public function getRoot(): self
    {
        return new self($this->uri->withPath('/'), $this->apiClient, $this->urlBuilder, $this->validator);
    }

    /**
     * Gets a Reference for the location at the specified relative path.
     *
     * The relative path can either be a simple child name (for example, "ada")
     * or a deeper slash-separated path (for example, "ada/name/first").
     *
     * @see https://firebase.google.com/docs/reference/js/firebase.database.Reference#child
     *
     * @throws InvalidArgumentException if the path is invalid
     */
    public function getChild(string $path): self
    {
        $childPath = sprintf('/%s/%s', trim($this->uri->getPath(), '/'), trim($path, '/'));

        try {
            return new self(
                $this->uri->withPath($childPath),
                $this->apiClient,
                $this->urlBuilder,
                $this->validator,
            );
        } catch (\InvalidArgumentException $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Generates a new Query object ordered by the specified child key.
     *
     * @see Query::orderByChild()
     */
    public function orderByChild(string $path): Query
    {
        return $this->query()->orderByChild($path);
    }

    /**
     * Generates a new Query object ordered by key.
     *
     * @see Query::orderByKey()
     */
    public function orderByKey(): Query
    {
        return $this->query()->orderByKey();
    }

    /**
     * Generates a new Query object ordered by child values.
     *
     * @see Query::orderByValue()
     */
    public function orderByValue(): Query
    {
        return $this->query()->orderByValue();
    }

    /**
     * Generates a new Query limited to the first specific number of children.
     *
     * @see Query::limitToFirst()
     */
    public function limitToFirst(int $limit): Query
    {
        return $this->query()->limitToFirst($limit);
    }

    /**
     * Generates a new Query object limited to the last specific number of children.
     *
     * @see Query::limitToLast()
     */
    public function limitToLast(int $limit): Query
    {
        return $this->query()->limitToLast($limit);
    }

    /**
     * Creates a Query with the specified starting point (inclusive).
     *
     * @see Query::startAt()
     */
    public function startAt(bool|string|int|float $value): Query
    {
        return $this->query()->startAt($value);
    }

    /**
     * Creates a Query with the specified starting point (exclusive).
     *
     * @see Query::startAfter()
     */
    public function startAfter(bool|string|int|float $value): Query
    {
        return $this->query()->startAfter($value);
    }

    /**
     * Creates a Query with the specified ending point (inclusive).
     *
     * @see Query::endAt()
     */
    public function endAt(bool|string|int|float $value): Query
    {
        return $this->query()->endAt($value);
    }

    /**
     * Creates a Query with the specified ending point (exclusive).
     *
     * @see Query::endBefore()
     */
    public function endBefore(bool|string|int|float $value): Query
    {
        return $this->query()->endBefore($value);
    }

    /**
     * Creates a Query which includes children which match the specified value.
     *
     * @see Query::equalTo()
     */
    public function equalTo(bool|string|int|float $value): Query
    {
        return $this->query()->equalTo($value);
    }

    /**
     * Creates a Query with shallow results.
     *
     * @see Query::shallow()
     */
    public function shallow(): Query
    {
        return $this->query()->shallow();
    }

    /**
     * Returns the keys of a reference's children.
     *
     * @throws DatabaseException if the API reported an error
     * @throws OutOfRangeException if the reference has no children with keys
     *
     * @return string[]
     */
    public function getChildKeys(): array
    {
        $snapshot = $this->shallow()->getSnapshot();

        if (is_array($value = $snapshot->getValue())) {
            return array_map('strval', array_keys($value));
        }

        throw new OutOfRangeException(sprintf('%s has no children with keys', $this));
    }

    /**
     * Convenience method for {@see getSnapshot()}->getValue().
     *
     * @throws DatabaseException if the API reported an error
     */
    public function getValue(): mixed
    {
        return $this->getSnapshot()->getValue();
    }

    /**
     * Write data to this database location.
     *
     * This will overwrite any data at this location and all child locations.
     *
     * Passing null for the new value is equivalent to calling {@see remove()}:
     * all data at this location or any child location will be deleted.
     *
     * @throws DatabaseException if the API reported an error
     */
    public function set(mixed $value): self
    {
        if ($value === null) {
            $this->apiClient->remove($this->uri->getPath());
        } else {
            $this->apiClient->set($this->uri->getPath(), $value);
        }

        return $this;
    }

    /**
     * Returns a data snapshot of the current location.
     *
     * @throws DatabaseException if the API reported an error
     */
    public function getSnapshot(): Snapshot
    {
        $value = $this->apiClient->get($this->uri->getPath());

        return new Snapshot($this, $value);
    }

    /**
     * Generates a new child location using a unique key and returns its reference.
     *
     * This is the most common pattern for adding data to a collection of items.
     *
     * If you provide a value to push(), the value will be written to the generated location.
     * If you don't pass a value, nothing will be written to the database and the child
     * will remain empty (but you can use the reference elsewhere).
     *
     * The unique key generated by push() are ordered by the current time, so the resulting
     * list of items will be chronologically sorted. The keys are also designed to be
     * unguessable (they contain 72 random bits of entropy).
     *
     * @see https://firebase.google.com/docs/reference/js/firebase.database.Reference#push
     *
     * @param mixed|null $value
     *
     * @throws DatabaseException if the API reported an error
     */
    public function push($value = null): self
    {
        $value ??= [];

        $newKey = $this->apiClient->push($this->uri->getPath(), $value);
        $newPath = sprintf('%s/%s', $this->uri->getPath(), $newKey);

        return new self($this->uri->withPath($newPath), $this->apiClient, $this->urlBuilder, $this->validator);
    }

    /**
     * Remove the data at this database location.
     *
     * Any data at child locations will also be deleted.
     *
     * @see https://firebase.google.com/docs/reference/js/firebase.database.Reference#remove
     *
     * @throws DatabaseException if the API reported an error
     */
    public function remove(): self
    {
        $this->apiClient->remove($this->uri->getPath());

        return $this;
    }

    /**
     * Remove the data at the given locations.
     *
     * Each location can either be a simple property (for example, "name"), or a relative path
     * (for example, "name/first") from the current location to the data to remove.
     *
     * Any data at child locations will also be deleted.
     *
     * @param string[] $keys Locations to remove
     *
     * @throws DatabaseException
     */
    public function removeChildren(array $keys): self
    {
        $this->update(
            array_fill_keys($keys, null),
        );

        return $this;
    }

    /**
     * Writes multiple values to the database at once.
     *
     * The values argument contains multiple property/value pairs that will be written to the database together.
     * Each child property can either be a simple property (for example, "name"), or a relative path
     * (for example, "name/first") from the current location to the data to update.
     *
     * As opposed to the {@see set()} method, update() can be use to selectively update only the referenced properties
     * at the current location (instead of replacing all the child properties at the current location).
     *
     * Passing null to {see update()} will remove the data at this location.
     *
     * @see https://firebase.google.com/docs/reference/js/firebase.database.Reference#update
     *
     * @param array<mixed> $values
     *
     * @throws DatabaseException if the API reported an error
     */
    public function update(array $values): self
    {
        $this->apiClient->update($this->uri->getPath(), $values);

        return $this;
    }

    /**
     * Returns the absolute URL for this location.
     *
     * This method returns a URL that is ready to be put into a browser, curl command, or a
     * {@see Database::getReferenceFromUrl()} call. Since all of those expect the URL
     * to be url-encoded, toString() returns an encoded URL.
     *
     * Append '.json' to the URL when typed into a browser to download JSON formatted data.
     * If the location is secured (not publicly readable),
     * you will get a permission-denied error.
     *
     * @see https://firebase.google.com/docs/reference/js/firebase.database.Reference#toString
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * Returns a new query for the current reference.
     */
    private function query(): Query
    {
        return new Query($this, $this->apiClient);
    }
}

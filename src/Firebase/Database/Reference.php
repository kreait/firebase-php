<?php

declare(strict_types=1);

namespace Kreait\Firebase\Database;

use Kreait\Firebase\Database\Reference\Validator;
use Kreait\Firebase\Exception\DatabaseException;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Exception\OutOfRangeException;
use Psr\Http\Message\UriInterface;

/**
 * A Reference represents a specific location in your database and can be used
 * for reading or writing data to that database location.
 *
 * @see https://firebase.google.com/docs/reference/js/firebase.database.Reference
 */
class Reference
{
    /**
     * @var UriInterface
     */
    private $uri;

    /**
     * @var ApiClient
     */
    private $apiClient;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @internal
     *
     * @throws InvalidArgumentException if the reference URI is invalid
     */
    public function __construct(UriInterface $uri, ApiClient $apiClient, Validator $validator = null)
    {
        $this->validator = $validator ?? new Validator();
        $this->validator->validateUri($uri);

        $this->uri = $uri;
        $this->apiClient = $apiClient;
    }

    /**
     * The last part of the current path.
     *
     * For example, "ada" is the key for https://sample-app.firebaseio.com/users/ada.
     *
     * The key of the root Reference is null.
     *
     * @see https://firebase.google.com/docs/reference/js/firebase.database.Reference#key
     *
     * @return string|null
     */
    public function getKey()
    {
        $key = \basename($this->getPath());

        return $key !== '' ? $key : null;
    }

    /**
     * Returns the full path to a reference.
     */
    public function getPath(): string
    {
        return \trim($this->uri->getPath(), '/');
    }

    /**
     * The parent location of a Reference.
     *
     * @see https://firebase.google.com/docs/reference/js/firebase.database.Reference#parent
     *
     * @throws OutOfRangeException if requested for the root Reference
     *
     * @return Reference
     */
    public function getParent(): self
    {
        $parentPath = \dirname($this->getPath());

        if ($parentPath === '.') {
            throw new OutOfRangeException('Cannot get parent of root reference');
        }

        return new self($this->uri->withPath($parentPath), $this->apiClient, $this->validator);
    }

    /**
     * The root location of a Reference.
     *
     * @see https://firebase.google.com/docs/reference/js/firebase.database.Reference#root
     *
     * @return Reference
     */
    public function getRoot(): self
    {
        return new self($this->uri->withPath('/'), $this->apiClient, $this->validator);
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
     *
     * @return Reference
     */
    public function getChild(string $path): self
    {
        $childPath = \sprintf('%s/%s', \trim($this->uri->getPath(), '/'), \trim($path, '/'));

        try {
            return new self($this->uri->withPath($childPath), $this->apiClient, $this->validator);
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
     * Creates a Query with the specified starting point.
     *
     * @see Query::startAt()
     *
     * @param int|float|string|bool $value $value
     */
    public function startAt($value): Query
    {
        return $this->query()->startAt($value);
    }

    /**
     * Creates a Query with the specified ending point.
     *
     * @see Query::endAt()
     *
     * @param int|float|string|bool $value
     */
    public function endAt($value): Query
    {
        return $this->query()->endAt($value);
    }

    /**
     * Creates a Query which includes children which match the specified value.
     *
     * @see Query::equalTo()
     *
     * @param int|float|string|bool $value
     */
    public function equalTo($value): Query
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
     * @throws OutOfRangeException if the reference has no children with keys
     * @throws DatabaseException if the API reported an error
     */
    public function getChildKeys(): array
    {
        $snapshot = $this->shallow()->getSnapshot();

        if (\is_array($value = $snapshot->getValue())) {
            return \array_keys($value);
        }

        throw new OutOfRangeException(\sprintf('%s has no children with keys', $this));
    }

    /**
     * Convenience method for {@see getSnapshot()}->getValue().
     *
     * @throws DatabaseException if the API reported an error
     *
     * @return mixed
     */
    public function getValue()
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
     * @param mixed $value
     *
     * @throws DatabaseException if the API reported an error
     *
     * @return Reference
     */
    public function set($value): self
    {
        if ($value === null) {
            $this->apiClient->remove($this->uri);
        } else {
            $this->apiClient->set($this->uri, $value);
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
        $value = $this->apiClient->get($this->uri);

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
     * @param mixed $value
     *
     * @throws DatabaseException if the API reported an error
     *
     * @return Reference A new reference for the added child
     */
    public function push($value = null): self
    {
        $value = $value ?? [];

        $newKey = $this->apiClient->push($this->uri, $value);
        $newPath = \sprintf('%s/%s', $this->uri->getPath(), $newKey);

        return new self($this->uri->withPath($newPath), $this->apiClient, $this->validator);
    }

    /**
     * Remove the data at this database location.
     *
     * Any data at child locations will also be deleted.
     *
     * @see https://firebase.google.com/docs/reference/js/firebase.database.Reference#remove
     *
     * @throws DatabaseException if the API reported an error
     *
     * @return Reference A new instance for the now empty Reference
     */
    public function remove(): self
    {
        $this->apiClient->remove($this->uri);

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
     * @throws DatabaseException if the API reported an error
     *
     * @return Reference
     */
    public function update(array $values): self
    {
        $this->apiClient->update($this->uri, $values);

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

    /**
     * Returns a new query for the current reference.
     */
    private function query(): Query
    {
        return new Query($this, $this->apiClient);
    }
}

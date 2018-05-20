<?php
/**
 * Copyright 2017 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Kreait\Firebase\Firestore;

use Google\Cloud\Core\Timestamp;

/**
 * Represents the data of a document at the time of retrieval.
 * A snapshot is immutable and may point to a non-existing document.
 *
 * Fields may be read in array-style syntax. Note that writing using array-style
 * syntax is NOT supported and will result in a `\BadMethodCallException`.
 *
 * Example:
 * ```
 * use Google\Cloud\Firestore\FirestoreClient;
 *
 * $firestore = new FirestoreClient();
 * $document = $firestore->document('users/john');
 * $snapshot = $document->snapshot();
 * ```
 *
 * ```
 * // Fields are exposed via array-style accessors:
 * $bitcoinWalletValue = $snapshot['wallet']['cryptoCurrency']['bitcoin'];
 * ```
 */
class Snapshot implements \ArrayAccess
{
    /**
     * @var DocumentReference
     */
    private $reference;

    /**
     * @var ValueMapper
     */
    private $valueMapper;

    /**
     * @var array
     */
    private $info;

    /**
     * @var array
     */
    private $data;

    /**
     * @var bool
     */
    private $exists;

    /**
     * @param DocumentReference $reference The document which created the snapshot.
     * @param ValueMapper $valueMapper A Firestore Value Mapper.
     * @param array $info Document information, such as create and update timestamps.
     * @param array $data Document field data.
     * @param bool $exists Whether the document exists in the Firestore database.
     */
    public function __construct(
        Document $reference,
        array $info,
        array $data,
        $exists
    ) {
        $this->reference = $reference;
        $this->info = $info;
        $this->data = $data;
        $this->exists = $exists;
    }

    /**
     * Get the reference of the document which created the snapshot.
     *
     * Example:
     * ```
     * $reference = $snapshot->reference();
     * ```
     *
     * @return DocumentReference
     */
    public function reference()
    {
        return $this->reference;
    }

    /**
     * Get the document name.
     *
     * Names are absolute. The result of this call would be of the form
     * `projects/<project-id>/databases/<database-id>/documents/<relative-path>`.
     *
     * Other methods are available to retrieve different parts of a collection name:
     * * {@see Google\Cloud\Firestore\DocumentSnapshot::id()} Returns the last element.
     * * {@see Google\Cloud\Firestore\DocumentSnapshot::path()} Returns the path, relative to the database.
     *
     * Example:
     * ```
     * $name = $snapshot->name();
     * ```
     *
     * @return string
     */
    public function name()
    {
        return $this->reference->name();
    }

    /**
     * Get the document path.
     *
     * Paths identify the location of a document, relative to the database name.
     *
     * To retrieve the document ID (the last element of the path), use
     * {@see Google\Cloud\Firestore\DocumentSnapshot::id()}.
     *
     * Example:
     * ```
     * $path = $snapshot->path();
     * ```
     *
     * @return string
     */
    public function path()
    {
        return $this->reference->path();
    }

    /**
     * Get the document identifier (i.e. the last path element).
     *
     * IDs are the path element which identifies a resource. To retrieve the
     * full path to a resource (the resource name), use
     * {@see Google\Cloud\Firestore\DocumentSnapshot::name()}.
     *
     * Example:
     * ```
     * $id = $snapshot->id();
     * ```
     *
     * @return string
     */
    public function id()
    {
        return $this->reference->id();
    }

    /**
     * Get the Document Update Timestamp.
     *
     * Example:
     * ```
     * $updateTime = $snapshot->updateTime();
     * ```
     *
     * @return Timestamp|null
     */
    public function updateTime()
    {
        return isset($this->info['updateTime'])
            ? $this->info['updateTime']
            : null;
    }

    /**
     * Get the Document Read Timestamp.
     *
     * Example:
     * ```
     * $readTime = $snapshot->readTime();
     * ```
     *
     * @return Timestamp|null
     */
    public function readTime()
    {
        return isset($this->info['readTime'])
            ? $this->info['readTime']
            : null;
    }

    /**
     * Get the Document Create Timestamp.
     *
     * Example:
     * ```
     * $createTime = $snapshot->createTime();
     * ```
     *
     * @return Timestamp|null
     */
    public function createTime()
    {
        return isset($this->info['createTime'])
            ? $this->info['createTime']
            : null;
    }

    /**
     * Returns document data as an array, or null if the document does not exist.
     *
     * Example:
     * ```
     * $data = $snapshot->data();
     * ```
     *
     * @return array|null
     */
    public function data()
    {
        return $this->exists
            ? $this->data
            : null;
    }

    /**
     * Returns true if the document exists in the database.
     *
     * Example:
     * ```
     * if ($snapshot->exists()) {
     *     echo "The document exists!";
     * }
     * ```
     *
     * @return bool
     */
    public function exists()
    {
        return $this->exists;
    }

    /**
     * Get a field by field path.
     *
     * A field path is a string containing the path to a specific field, at the
     * top level or nested, delimited by `.`. For instance, the value `hello` in
     * the structured field `{ "foo" : { "bar" : "hello" }}` would be accessible
     * using a field path of `foo.bar`.
     *
     * Example:
     * ```
     * $value = $snapshot->get('wallet.cryptoCurrency.bitcoin');
     * ```
     *
     * ```
     * // Field names containing dots or symbols can be targeted using a FieldPath instance:
     * use Google\Cloud\Firestore\FieldPath;
     *
     * $value = $snapshot->get(new FieldPath(['wallet', 'cryptoCurrency', 'my.coin']));
     * ```
     *
     * @param string|FieldPath $fieldPath The field path to return.
     * @return mixed
     * @throws \InvalidArgumentException if the field path does not exist.
     */
    public function get($fieldPath)
    {
        $res = null;

        if (is_string($fieldPath)) {
            $parts = explode('.', $fieldPath);
        } elseif ($fieldPath instanceof FieldPath) {
            $parts = $fieldPath->path();
        } else {
            throw new \InvalidArgumentException('Given path was not a string or instance of FieldPath.');
        }

        $len = count($parts);

        $fields = $this->data;
        foreach ($parts as $idx => $part) {
            if ($idx === $len-1 && isset($fields[$part])) {
                $res = $fields[$part];
                break;
            } else {
                if (!isset($fields[$part])) {
                    throw new \InvalidArgumentException('field path does not exist.');
                }

                $fields = $fields[$part];
            }
        }

        return $res;
    }

    /**
     * @access private
     */
    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException('DocumentSnapshots are read-only.');
    }

    /**
     * @access private
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * @access private
     */
    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('DocumentSnapshots are read-only.');
    }

    /**
     * @access private
     */
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            trigger_error(sprintf(
                'Undefined index: %s. Document field does not exist.',
                $offset
            ), E_USER_NOTICE);

            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        return $this->data[$offset];
    }
}

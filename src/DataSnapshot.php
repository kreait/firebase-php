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
 * Data Snapshot.
 *
 * DataSnapshots are efficiently-generated immutable copies of the data at a Firebase location.
 * They can't be modified and will never change. To modify data, you always use a Firebase reference.
 *
 * @link https://www.firebase.com/docs/web/api/datasnapshot/ DataSnapshot
 */
class DataSnapshot
{
    /**
     * The reference that generated this snapshot.
     *
     * @var ReferenceInterface
     */
    private $reference;

    /**
     * The data.
     *
     * @var mixed
     */
    private $data;

    /**
     * Constructor.
     *
     * @param ReferenceInterface $reference
     * @param mixed|null         $data
     */
    public function __construct(ReferenceInterface $reference, $data = null)
    {
        $this->reference = $reference;

        if (is_object($data)) {
            $data = json_decode(json_encode($data), true);
        }

        $this->data = $data;
    }

    /**
     * Gets the key of the location that generated this snapshot.
     *
     * @return string The key of the location that generated this snapshot.
     */
    public function key()
    {
        return $this->reference->getKey();
    }

    /**
     * Alias for {@see key()}.
     *
     * @return string The key of the location that generated this snapshot.
     */
    public function name()
    {
        return $this->key();
    }

    /**
     * Gets the Firebase reference for the location that generated this snapshot.
     *
     * @return ReferenceInterface The Reference for the location that generated this snapshot.
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Returns true if the snapshot contains a non-null value.
     *
     * It is purely a convenience function, as snapshot.exists() is equivalent to snapshot.val() != null.
     *
     * @return bool true if the snapshot contains a non-null value, else false.
     */
    public function exists()
    {
        return !!$this->val();
    }

    /**
     * Returns a PHP native representation of the snapshot.
     *
     * This can either
     *
     * @return object|string|number|bool|null
     */
    public function val()
    {
        return json_decode(json_encode($this->data), false);
    }

    /**
     * Returns true if the specified child exists.
     *
     * @param string $path A relative path to the location of a potential child.
     *
     * @return bool
     */
    public function hasChild($path)
    {
        return null !== $this->searchChildByPath($path);
    }

    /**
     * Returns true if the DataSnapshot has any children.
     *
     * @return bool true if this snapshot has any children; else false.
     */
    public function hasChildren()
    {
        return null !== $this->search('*');
    }

    /**
     * Gets the number of children for this DataSnapshot.
     *
     * @return int The number of children.
     */
    public function numChildren()
    {
        return count($this->search('*'));
    }

    /**
     * Gets a DataSnapshot for the location at the specified relative path.
     *
     * @param string $path A relative path to the location of child data.
     *
     * @return DataSnapshot|null The new DataSnapshot or null if the child does not exist.
     */
    public function child($path)
    {
        if ($child = $this->searchChildByPath($path)) {
            return new self($this->reference->getReference($path), $child);
        }

        return null;
    }

    /**
     * @param string $path A relative path to the location of a potential child.
     *
     * @return mixed|null
     */
    private function searchChildByPath($path)
    {
        $expression = implode('.', explode('/', $path));

        return $this->search($expression);
    }

    /**
     * @param string $expression A relative path to the location of a potential child.
     *
     * @return mixed|null
     */
    private function search($expression)
    {
        if (!is_array($this->data)) {
            return null;
        }

        return \JmesPath\Env::search($expression, $this->data);
    }
}

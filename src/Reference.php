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

class Reference implements ReferenceInterface
{
    /**
     * The Firebase.
     *
     * @var FirebaseInterface
     */
    private $firebase;

    /**
     * The reference's location.
     *
     * @var string
     */
    private $location;

    /**
     * The last token in the Reference's location.
     *
     * @var string
     */
    private $key;

    /**
     * The Reference's data.
     *
     * @var mixed
     */
    private $data;

    /**
     * Initialize the Reference.
     *
     * @param FirebaseInterface $firebase The Firebase instance.
     * @param string            $location The Reference location.
     */
    public function __construct(FirebaseInterface $firebase, $location)
    {
        $this->firebase = $firebase;
        $this->location = Utils::normalizeLocation($location);
        $this->data = [];
    }

    public function getKey()
    {
        if (!$this->key) {
            $parts = explode('/', $this->location);
            $this->key = array_pop($parts);
        }

        return $this->key;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function getReference($location)
    {
        return $this->firebase->getReference(sprintf('%s/%s', $this->location, Utils::normalizeLocation($location)));
    }

    public function getData()
    {
        if (empty($this->data)) {
            $this->data = $this->firebase->get($this->location);
        }

        return $this->data;
    }

    public function query(Query $query)
    {
        return $this->firebase->query($this->location, $query);
    }

    public function set($data)
    {
        $writtenData = $this->firebase->set($data, $this->location);
        $this->data = $writtenData ?: [];

        return $this;
    }

    public function push($data)
    {
        $newKey = $this->firebase->push($data, $this->location);
        $this->data = []; // Reset data, because it now contains new data

        return $this->getReference($newKey);
    }

    public function update($data)
    {
        $writtenData = $this->firebase->update($data, $this->location);
        $this->updateData($writtenData);

        return $this;
    }

    public function delete()
    {
        $this->firebase->delete($this->location);
    }

    private function updateData(array $data)
    {
        $data = array_merge($this->data, $data);

        $this->data = $this->arrayFilterRecursive($data); // Remove all null values
    }

    public function offsetExists($offset)
    {
        $this->getData(); // Ensure data exists
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        $this->getData(); // Ensure data exists
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
        $this->update([$offset => $value]);
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function count()
    {
        return count($this->data);
    }

    /**
     * Recursive version of "array_filter" function.
     *
     * @param mixed $haystack Array to filter.
     * @return mixed Filtered result.
     */
    private function arrayFilterRecursive($haystack)
    {
        foreach ($haystack as $key => $value) {

            if (is_array($value)) {
                $haystack[$key] = $this->arrayFilterRecursive($haystack[$key]);
            }

            if (empty($haystack[$key])) {
                unset($haystack[$key]);
            }
        }

        return $haystack;
    }
}

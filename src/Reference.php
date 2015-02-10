<?php
/**
 * This file is part of the firebase-php package.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */
namespace Kreait\Firebase;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class Reference implements ReferenceInterface
{
    use LoggerAwareTrait;

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
     * @var array
     */
    private $data;

    /**
     * Initialize the Reference.
     *
     * @param FirebaseInterface $firebase The Firebase instance.
     * @param string $location The Reference location.
     */
    public function __construct(FirebaseInterface $firebase, $location)
    {
        $this->logger = new NullLogger();
        $this->firebase = $firebase;
        $this->location = Utils::normalizeLocation($location);
        $this->data = [];
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        if (!$this->key) {
            $parts = explode('/', $this->location);
            $this->key = array_pop($parts);
        }

        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function getReference($location)
    {
        return $this->firebase->getReference(sprintf('%s/%s', $this->location, Utils::normalizeLocation($location)));
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        if (!empty($this->data)) {
            return $this->data;
        }

        return $this->data = $this->firebase->get($this->location);
    }

    /**
     * {@inheritdoc}
     */
    public function query(Query $query)
    {
        return $this->firebase->query($this->location, $query);
    }

    /**
     * {@inheritdoc}
     */
    public function set($data)
    {
        $writtenData = $this->firebase->set($data, $this->location);
        $this->updateData($writtenData, $merge = false);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function push($data)
    {
        $newKey = $this->firebase->push($data, $this->location);

        return $this->getReference($newKey);
    }

    /**
     * {@inheritdoc}
     */
    public function update($data)
    {
        $writtenData = $this->firebase->update($data, $this->location);
        $this->updateData($writtenData, $merge = true);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        $this->firebase->delete($this->location);
    }

    private function updateData($data, $merge = false)
    {
        if ($merge) {
            $this->data = array_merge($this->data, $data);
        } else {
            $this->data = $data;
        }

        $this->data = array_filter($this->data, 'strlen'); // Remove all null values
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        $this->getData(); // Ensure data exists
        return isset($this->data[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        $this->getData(); // Ensure data exists
        return $this->data[$offset];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
        $this->update([$offset => $value]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->data);
    }
}

<?php
/**
 * This file is part of the firebase-php package.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Kreait\Firebase;

use Psr\Log\LoggerAwareTrait;

class Reference implements ReferenceInterface
{
    use LoggerAwareTrait;

    /**
     * @var FirebaseInterface
     */
    private $firebase;

    /**
     * @var string
     */
    private $referenceUrl;

    public function __construct(FirebaseInterface $firebase, $location)
    {
        $this->firebase = $firebase;
        $this->referenceUrl = Utils::normalizeLocation($location);
        $this->logger = $firebase->getLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUrl()
    {
        return sprintf('%s/%s', $this->firebase->getBaseUrl(), $this->referenceUrl);
    }

    /**
     * {@inheritdoc}
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * {@inheritdoc}
     */
    public function set($data, $location = null)
    {
        $fullLocation = Utils::normalizeLocation(sprintf('%s/%s', $this->referenceUrl, $location));

        return $this->firebase->set($data, $fullLocation);
    }

    /**
     * {@inheritdoc}
     */
    public function push($data, $location = null)
    {
        $fullLocation = Utils::normalizeLocation(sprintf('%s/%s', $this->referenceUrl, $location));

        return $this->firebase->push($data, $fullLocation);
    }

    /**
     * {@inheritdoc}
     */
    public function update($data, $location = null)
    {
        $fullLocation = Utils::normalizeLocation(sprintf('%s/%s', $this->referenceUrl, $location));

        return $this->firebase->update($data, $fullLocation);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($location = null)
    {
        $fullLocation = Utils::normalizeLocation(sprintf('%s/%s', $this->referenceUrl, $location));

        $this->firebase->delete($fullLocation);
    }

    /**
     * {@inheritdoc}
     */
    public function get($location = null, array $options = [])
    {
        $fullLocation = Utils::normalizeLocation(sprintf('%s/%s', $this->referenceUrl, $location));

        return $this->firebase->get($fullLocation, $options);
    }
}

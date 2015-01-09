<?php
/**
 * This file is part of the firebase-php package.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Kreait\Firebase;

use Psr\Log\NullLogger;

class Reference implements ReferenceInterface
{
    use \Psr\Log\LoggerAwareTrait;

    /**
     * @var Firebase
     */
    private $firebase;

    /**
     * @var string
     */
    private $referenceUrl;

    public function __construct(Firebase $firebase, $referenceUrl)
    {
        $this->firebase = $firebase;
        $this->referenceUrl = Utils::normalizeLocation($referenceUrl);
        $this->logger = new NullLogger();
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

        return $this->firebase->delete($fullLocation);
    }

    /**
     * {@inheritdoc}
     */
    public function get($location = null)
    {
        $fullLocation = Utils::normalizeLocation(sprintf('%s/%s', $this->referenceUrl, $location));

        return $this->firebase->get($fullLocation);
    }
}

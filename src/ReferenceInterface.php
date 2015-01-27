<?php
/**
 * This file is part of the firebase-php package.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Kreait\Firebase;

use Psr\Log\LoggerAwareInterface;

interface ReferenceInterface extends LoggerAwareInterface, ReferenceProviderInterface, \ArrayAccess, \Countable
{
    /**
     * Returns the Reference's data
     *
     * @return array
     */
    public function getData();

    /**
     * Writes data to this Reference.
     *
     * @param array $data
     * @return $this
     */
    public function set($data);

    /**
     * Generates a new child location using a unique key and returns a Reference to it.
     *
     * @param array $data
     * @return ReferenceInterface
     */
    public function push($data);

    /**
     * Writes the given children to this location.
     *
     * @param array $data
     * @return $this
     */
    public function update($data);

    /**
     * Deletes the Reference.
     */
    public function delete();

    /**
     * Returns the last token in the Reference's location.
     *
     * @return string
     */
    public function getKey();
}

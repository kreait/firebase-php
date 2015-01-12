<?php
/**
 * This file is part of the firebase-php package.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Kreait\Firebase;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

interface FirebaseInterface extends LoggerAwareInterface
{
    /**
     * Returns the base URL.
     *
     * @return string
     */
    public function getBaseUrl();

    /**
     * Returns the logger.
     *
     * @return LoggerInterface
     */
    public function getLogger();

    /**
     * Returns the data at the given location, or null if not defined.
     *
     * @param  string|null $location The location.
     * @param  array $options The options.
     * @return array|null  The data at the given location, or null if not defined.
     */
    public function get($location, array $options = []);

    /**
     * Write or replace data at the given location.
     *
     * @param  array|object $data
     * @param  string       $location
     * @return array        The written fields. Submitted empty fields are omitted.
     */
    public function set($data, $location);

    /**
     * Generates a new child and returns its key.
     *
     * @param  array|object $data The data to be pushed.
     * @param  string       $location The location to push the new child to.
     * @return string       The new child's key.
     */
    public function push($data, $location);

    /**
     * Update the given field(s) at the given location.
     *
     * @param  array|object $data The fields
     * @param  string       $location
     * @return array        The written fields. Submitted empty fields are omitted.
     */
    public function update($data, $location);

    /**
     * Deletes the given location.
     *
     * @param  string $location
     * @return void
     */
    public function delete($location);
}

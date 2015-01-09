<?php
/**
 * This file is part of the firebase-php package.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Kreait\Firebase;

use Psr\Log\LoggerAwareInterface;

interface FirebaseInterface extends LoggerAwareInterface
{
    const MAX_TREE_DEPTH = 32;
    const MAX_NODE_KEY_LENGTH_IN_BYTES = 768;
    const FORBIDDEN_NODE_KEY_CHARS = '.$#[]';
    /**
     * Returns the base URL.
     *
     * @return string
     */
    public function getBaseUrl();

    /**
     * Returns the data at the given location, or null if not defined.
     *
     * @param  string|null $location The location.
     * @return array|null  The data at the given location, or null if not defined.
     */
    public function get($location = null);

    /**
     * Write or replace data at the given location.
     *
     * @param  array|object $data
     * @param  string       $location
     * @return array        The written fields. Submitted empty fields are omitted.
     */
    public function set($data, $location);

    /**
     * Generates a new child location using a unique key and returns the key.
     *
     * @param  array|object $data
     * @param  string       $location
     * @return string       The child key.
     */
    public function push($data, $location);

    /**
     * Update some of the keys for a defined path without replacing all of the data.
     *
     * @param  array|object $data
     * @param  string       $location
     * @return array        The written fields. Submitted empty fields are omitted.
     */
    public function update($data, $location);

    /**
     * Deletes the given location. If null,
     *
     * @param  string $location
     * @return void
     */
    public function delete($location);
}

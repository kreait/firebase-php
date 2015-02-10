<?php
/**
 * This file is part of the firebase-php package.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */
namespace Kreait\Firebase;

use Kreait\Firebase\Exception\FirebaseException;
use Psr\Log\LoggerAwareInterface;

interface FirebaseInterface extends LoggerAwareInterface, ReferenceProviderInterface
{
    /**
     * Returns the data of the given location.
     *
     * @param string $location The location.
     *
     * @throws FirebaseException When the location is not available.
     *
     * @return array The returned data.
     */
    public function get($location);

    /**
     * Queries the given location.
     *
     * @param  string $location The location.
     * @param  Query  $query    The query.
     * @return array  The data.
     */
    public function query($location, Query $query);

    /**
     * Write or replace data at the given location.
     *
     * @param  array  $data
     * @param  string $location
     * @return array  The returned data.
     */
    public function set(array $data, $location);

    /**
     * Generates a new child and returns its key.
     *
     * @param  array  $data     The data to be pushed.
     * @param  string $location The location to push the new child to.
     * @return string The key of the new child.
     */
    public function push(array $data, $location);

    /**
     * Update the given field(s) at the given location.
     *
     * @param  array  $data     The fields.
     * @param  string $location The location.
     * @return array  The written fields.
     */
    public function update(array $data, $location);

    /**
     * Deletes the given location.
     *
     * @param  string $location
     * @return void
     */
    public function delete($location);
}

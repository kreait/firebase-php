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
     * @return mixed The returned data.
     */
    public function get($location);

    /**
     * Queries the given location.
     *
     * @param string $location The location.
     * @param Query  $query    The query.
     *
     * @return mixed The data.
     */
    public function query($location, Query $query);

    /**
     * Write or replace data at the given location.
     *
     * @param array  $data
     * @param string $location
     *
     * @return array The returned data.
     */
    public function set(array $data, $location);

    /**
     * Generates a new child and returns its key.
     *
     * @param array  $data     The data to be pushed.
     * @param string $location The location to push the new child to.
     *
     * @return string The key of the new child.
     */
    public function push(array $data, $location);

    /**
     * Update the given field(s) at the given location.
     *
     * @param array  $data     The fields.
     * @param string $location The location.
     *
     * @return array The written fields.
     */
    public function update(array $data, $location);

    /**
     * Deletes the given location.
     *
     * @param string $location
     */
    public function delete($location);
}

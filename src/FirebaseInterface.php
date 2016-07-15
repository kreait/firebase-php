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

interface FirebaseInterface extends ReferenceProviderInterface
{
    /**
     * Returns the base url.
     *
     * @return string
     */
    public function getBaseUrl();

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
     * @param array|object $data
     * @param string       $location
     *
     * @return array The returned data.
     */
    public function set($data, $location);

    /**
     * Generates a new child and returns its key.
     *
     * @param array|object $data     The data to be pushed.
     * @param string       $location The location to push the new child to.
     *
     * @return string The key of the new child.
     */
    public function push($data, $location);

    /**
     * Update the given field(s) at the given location.
     *
     * @param array|object $data     The fields.
     * @param string       $location The location.
     *
     * @return array The written fields.
     */
    public function update($data, $location);

    /**
     * Deletes the given location.
     *
     * @param string $location
     */
    public function delete($location);

    /**
     * Sets the Firebase client configuration.
     *
     * @param ConfigurationInterface $configuration The Firebase configuration
     */
    public function setConfiguration(ConfigurationInterface $configuration);

    /**
     * Returns the Firebase client configuration.
     *
     * @return ConfigurationInterface
     */
    public function getConfiguration();

    /**
     * Sets the given authentication override credentials.
     *
     * @param string $uid
     * @param array $claims
     */
    public function setAuthOverride($uid, array $claims = []);

    /**
     * Removes authentication override credentials.
     *
     * @return static
     */
    public function removeAuthOverride();
}

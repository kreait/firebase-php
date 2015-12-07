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

interface ReferenceInterface extends ReferenceProviderInterface, \ArrayAccess, \Countable
{
    /**
     * Returns the Reference's data.
     *
     * @return mixed The data.
     */
    public function getData();

    /**
     * Queries the Reference.
     *
     * @param Query $query The query.
     *
     * @return mixed The data.
     */
    public function query(Query $query);

    /**
     * Writes data to this Reference.
     *
     * @param array $data
     *
     * @return $this
     */
    public function set($data);

    /**
     * Generates a new child location using a unique key and returns a Reference to it.
     *
     * @param array $data
     *
     * @return ReferenceInterface
     */
    public function push($data);

    /**
     * Writes the given children to this location.
     *
     * @param array $data
     *
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

    /**
     * Returns the Reference's full location.
     *
     * @return string
     */
    public function getLocation();
}

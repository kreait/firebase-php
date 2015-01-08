<?php
/**
 * This file is part of the firebase-php package.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Kreait\Firebase;

interface ReferenceInterface extends FirebaseInterface
{
    /**
     * {@inheritdoc}
     */
    public function get($location = null);

    /**
     * {@inheritdoc}
     */
    public function set($data, $location = null);

    /**
     * {@inheritdoc}
     */
    public function push($data, $location = null);

    /**
     * {@inheritdoc}
     */
    public function update($data, $location = null);

    /**
     * {@inheritdoc}
     */
    public function delete($location = null);
}

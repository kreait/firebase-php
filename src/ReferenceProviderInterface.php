<?php
/**
 * This file is part of the firebase-php package.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */
namespace Kreait\Firebase;

interface ReferenceProviderInterface
{
    /**
     * Returns a reference to the given location.
     *
     * @param  string             $location
     * @return ReferenceInterface
     */
    public function getReference($location);
}

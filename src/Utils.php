<?php
/**
 * This file is part of the firebase-php package.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Kreait\Firebase;

class Utils
{
    /**
     * Checks validity of a Firebase base URL
     *
     * @param  string            $url
     * @return string
     * @throws FirebaseException
     */
    public static function normalizeBaseUrl($url)
    {
        FirebaseRestrictions::checkBaseUrl($url);

        return rtrim($url, '/');
    }

    /**
     * Returns a normalized location.
     *
     * @param string $location
     *
     * @throws FirebaseException If the location violates restrictions imposed by Firebase.
     *
     * @return string The normalized location.
     */
    public static function normalizeLocation($location)
    {
        $location = trim($location, '/');
        FirebaseRestrictions::checkLocation($location);

        return implode('/', explode('/', $location));
    }
}

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

class Utils
{
    /**
     * Checks validity of a Firebase base URL.
     *
     * @param string $url
     *
     * @throws FirebaseException
     *
     * @return string
     */
    public static function normalizeBaseUrl($url)
    {
        Restrictions::checkBaseUrl($url);

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
        $location = trim((string) $location, '/');
        Restrictions::checkLocation($location);

        return implode('/', explode('/', $location));
    }
}

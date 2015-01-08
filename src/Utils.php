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
        if ((($parts = parse_url($url)) === false) || !isset($parts['scheme']) || !isset($parts['host'])) {
            throw FirebaseException::baseUrlIsInvalid($url);
        }

        if ($parts['scheme'] !== 'https') {
            throw FirebaseException::baseUrlSchemeMustBeHttps($url);
        }

        return rtrim($url, '/');
    }

    /**
     * Returns a normalized location.
     *
     * @param string $location
     *
     * @throws FirebaseException If the location has not the right format or is deeper than 32 levels.
     *
     * @link https://www.firebase.com/docs/web/guide/understanding-data.html Data limits
     *
     * @return string The normalized location.
     */
    public static function normalizeLocation($location)
    {
        $parts = explode('/', trim($location, '/'));

        if (($count = count($parts)) > FirebaseInterface::MAX_TREE_DEPTH) {
            throw FirebaseException::locationKeyHasTooManyLevels(FirebaseInterface::MAX_TREE_DEPTH, $count);
        }

        array_walk($parts, ['self', 'validateNodeKey']);

        return implode('/', $parts);
    }

    private static function validateNodeKey($key)
    {
        $pattern = sprintf('/[%s]/', preg_quote(FirebaseInterface::FORBIDDEN_NODE_KEY_CHARS, '/'));

        if (preg_match($pattern, $key)) {
            throw FirebaseException::nodeKeyContainsForbiddenChars($key, FirebaseInterface::FORBIDDEN_NODE_KEY_CHARS);
        }

        if (($length = mb_strlen($key, '8bit')) > FirebaseInterface::MAX_NODE_KEY_LENGTH_IN_BYTES) {
            throw FirebaseException::locationKeyIsTooLong(FirebaseInterface::MAX_NODE_KEY_LENGTH_IN_BYTES, $length);
        }
    }
}

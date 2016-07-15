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

/**
 * @link https://www.firebase.com/docs/rest/guide/understanding-data.html#section-limitations Limitations and Restrictions
 */
class Restrictions
{
    // Location
    const MAXIMUM_DEPTH_OF_CHILD_NODES = 32;
    const KEY_LENGTH_IN_BYTES = 768;
    const FORBIDDEN_NODE_KEY_CHARS = '.$#[]';

    // Body
    const BODY_SIZE_IN_MB = 256;

    public static function checkBaseUrl($url)
    {
        self::checkValidity($url);

        if ('https' !== parse_url($url, PHP_URL_SCHEME)) {
            throw new FirebaseException(sprintf('The base url must point to an https URL, "%s" given.', $url));
        }
    }

    public static function checkLocation($location)
    {
        self::checkLocationDepth($location);
        self::checkLocationKeys($location);
    }

    private static function checkValidity($url)
    {
        $parts = parse_url($url);

        if (!$parts || !array_key_exists('scheme', $parts) || !array_key_exists('host', $parts)) {
            throw new FirebaseException(sprintf('The url "%s" is invalid.', $url));
        }
    }

    /**
     * Performs checks on a location's keys.
     *
     * @param string $location The location.
     *
     * @throws FirebaseException if a key violates a restriction.
     */
    private static function checkLocationKeys($location)
    {
        $parts = explode('/', trim($location, '/'));

        foreach ($parts as $key) {
            self::checkKeyLength($key);
            self::checkForForbiddenChars($key);
        }
    }

    /**
     * Checks if the given location exceeds the maximum depth, i.e. the number of location paths.
     *
     * @param string $location The location.
     *
     * @throws FirebaseException When the location exceeds the maximum depth.
     */
    private static function checkLocationDepth($location)
    {
        $parts = explode('/', trim($location, '/'));

        if (($count = count($parts)) > self::MAXIMUM_DEPTH_OF_CHILD_NODES) {
            throw new FirebaseException(sprintf(
                'A location key must not have more than %s keys, %s given.',
                self::MAXIMUM_DEPTH_OF_CHILD_NODES, $count
            ));
        }
    }

    /**
     * Checks if the given key exceeds the maximum key length.
     *
     * @param string $key The key.
     *
     * @throws FirebaseException When the key exceeds the maximum key length.
     */
    private static function checkKeyLength($key)
    {
        if (($length = mb_strlen($key, '8bit')) > self::KEY_LENGTH_IN_BYTES) {
            throw new FirebaseException(sprintf(
                'A location key must not be longer than %s bytes, %s bytes given.', self::KEY_LENGTH_IN_BYTES, $length
            ));
        }
    }

    /**
     * Checks if the given key includes forbidden characters.
     *
     * @param string $key The key.
     *
     * @throws FirebaseException When the key includes forbidden characters.
     */
    private static function checkForForbiddenChars($key)
    {
        $pattern = sprintf('/[%s]/', preg_quote(self::FORBIDDEN_NODE_KEY_CHARS, '/'));

        if (preg_match($pattern, $key)) {
            throw new FirebaseException(sprintf(
                'The location key "%s" contains on of the following invalid characters: %s',
                $key, self::FORBIDDEN_NODE_KEY_CHARS
            ));
        }
    }
}

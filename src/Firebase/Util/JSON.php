<?php

namespace Kreait\Firebase\Util;

use Kreait\Firebase\Exception\InvalidArgumentException;

class JSON
{
    /**
     * Wrapper for JSON encoding that throws when an error occurs.
     *
     * Shamelessly copied from Guzzle.
     *
     * @see \GuzzleHttp\json_encode()
     *
     * @param mixed $value   The value being encoded
     * @param int    $options JSON encode option bitmask
     * @param int    $depth   Set the maximum depth. Must be greater than zero
     *
     * @throws InvalidArgumentException if the JSON cannot be encoded
     *
     * @return string
     */
    public static function encode($value, $options = 0, $depth = 512): string
    {
        $json = \json_encode($value, $options, $depth);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException(
                'json_encode error: '.json_last_error_msg());
        }

        return $json;
    }

    /**
     * Wrapper for json_decode that throws when an error occurs.
     *
     * Shamelessly copied from Guzzle.
     *
     * @see \GuzzleHttp\json_encode()
     *
     * @param string $json JSON data to parse
     * @param bool $assoc  When true, returned objects will be converted into associative arrays
     * @param int $depth User specified recursion depth
     * @param int $options Bitmask of JSON decode options
     *
     * @throws \InvalidArgumentException if the JSON cannot be decoded
     *
     * @return mixed
     */
    public static function decode($json, $assoc = false, $depth = 512, $options = 0)
    {
        $data = \json_decode($json, $assoc, $depth, $options);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException(
                'json_decode error: '.json_last_error_msg());
        }

        return $data;
    }

    /**
     * Returns true if the given value is a valid JSON string.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public static function isValid($value): bool
    {
        try {
            self::decode($value);

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function prettyPrint($value): string
    {
        return self::encode($value, JSON_PRETTY_PRINT);
    }
}

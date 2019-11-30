<?php

declare(strict_types=1);

namespace Kreait\Firebase\Util;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Throwable;

class JSON
{
    /**
     * Wrapper for JSON encoding that throws when an error occurs.
     *
     * Shamelessly copied from Guzzle.
     *
     * @internal
     *
     * @see \GuzzleHttp\json_encode()
     *
     * @param mixed $value   The value being encoded
     * @param int $options JSON encode option bitmask
     * @param int $depth   Set the maximum depth. Must be greater than zero
     *
     * @throws InvalidArgumentException if the JSON cannot be encoded
     */
    public static function encode($value, int $options = null, int $depth = null): string
    {
        $options = $options ?? 0;
        $depth = $depth ?? 512;

        $json = \json_encode($value, $options, $depth);
        if (\json_last_error() !== \JSON_ERROR_NONE) {
            throw new InvalidArgumentException(
                'json_encode error: '.\json_last_error_msg());
        }

        return (string) $json;
    }

    /**
     * Wrapper for json_decode that throws when an error occurs.
     *
     * Shamelessly copied from Guzzle.
     *
     * @internal
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
    public static function decode($json, $assoc = null, $depth = null, $options = null)
    {
        $data = \json_decode($json, $assoc ?? false, $depth ?? 512, $options ?? 0);
        if (\json_last_error() !== \JSON_ERROR_NONE) {
            throw new InvalidArgumentException(
                'json_decode error: '.\json_last_error_msg());
        }

        return $data;
    }

    /**
     * Returns true if the given value is a valid JSON string.
     *
     * @internal
     *
     * @param mixed $value
     */
    public static function isValid($value): bool
    {
        try {
            self::decode($value);

            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * @internal
     *
     * @param mixed $value
     */
    public static function prettyPrint($value): string
    {
        return self::encode($value, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES);
    }
}

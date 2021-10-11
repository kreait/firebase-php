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
     * @param mixed $value The value being encoded
     * @param int|null $options JSON encode option bitmask
     * @param int|null $depth Set the maximum depth. Must be greater than zero
     *
     * @throws InvalidArgumentException if the JSON cannot be encoded
     */
    public static function encode($value, ?int $options = null, ?int $depth = null): string
    {
        $options ??= 0;
        $depth ??= 512;

        try {
            return \json_encode($value, JSON_THROW_ON_ERROR | $options, $depth);
        } catch (Throwable $e) {
            throw new InvalidArgumentException('json_encode error: '.$e->getMessage());
        }
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
     * @param bool|null $assoc When true, returned objects will be converted into associative arrays
     * @param int|null $depth User specified recursion depth
     * @param int|null $options Bitmask of JSON decode options
     *
     * @throws \InvalidArgumentException if the JSON cannot be decoded
     *
     * @return mixed
     */
    public static function decode(string $json, ?bool $assoc = null, ?int $depth = null, ?int $options = null)
    {
        $assoc ??= false;
        $depth ??= 512;
        $options ??= 0;

        try {
            return \json_decode($json, $assoc, $depth, JSON_THROW_ON_ERROR | $options);
        } catch (Throwable $e) {
            throw new InvalidArgumentException('json_decode error: '.$e->getMessage());
        }
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
        return self::encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}

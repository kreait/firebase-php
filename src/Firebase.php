<?php

use Firebase\V2;
use Firebase\V3;
use Psr\Http\Message\UriInterface;

/**
 * Convenience class to comfortably create new Firebase instances.
 */
final class Firebase
{
    /**
     * Creates a new Firebase V3 instance.
     *
     * @deprecated 2.2 will be removed in 3.0, use Firebase\Factory instead
     *
     * @param mixed $serviceAccount Service Account (ServiceAccount instance, JSON, array, path to JSON file)
     * @param string|UriInterface|null $databaseUri Database URI
     *
     * @throws \Firebase\Exception\InvalidArgumentException
     *
     * @return V3\Firebase
     */
    public static function fromServiceAccount($serviceAccount, $databaseUri = null): V3\Firebase
    {
        trigger_error(
            'This method is deprecated and will be removed in release 3.0 of this library.'
            .' Use Firebase\Factory instead.', E_USER_DEPRECATED
        );

        return V3\Firebase::fromServiceAccount($serviceAccount, $databaseUri);
    }

    /**
     * Creates a new Firebase V2 instance.
     *
     * @deprecated 2.2 will be removed in 3.0
     *
     * @param string|UriInterface $databaseUri Database URI as a string or an instance of UriInterface
     * @param string $secret
     *
     * @throws \Firebase\Exception\InvalidArgumentException
     *
     * @return V2\Firebase
     */
    public static function fromDatabaseUriAndSecret($databaseUri, string $secret): V2\Firebase
    {
        trigger_error(
            'This method is deprecated and will be removed in release 3.0 of this library.',
            E_USER_DEPRECATED
        );

        return V2\Firebase::fromDatabaseUriAndSecret($databaseUri, $secret);
    }
}

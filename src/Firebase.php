<?php

use Firebase\ServiceAccount;
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
     * @param mixed $serviceAccount Service Account (ServiceAccount instance, JSON, array, path to JSON file)
     * @param string|UriInterface|null $databaseUri Database URI
     *
     * @throws \Firebase\Exception\InvalidArgumentException
     *
     * @return V3\Firebase
     */
    public static function fromServiceAccount($serviceAccount, $databaseUri = null): V3\Firebase
    {
        return V3\Firebase::fromServiceAccount($serviceAccount, $databaseUri);
    }

    /**
     * Creates a new Firebase V2 instance.
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
        return V2\Firebase::fromDatabaseUriAndSecret($databaseUri, $secret);
    }
}

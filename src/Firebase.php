<?php

use Firebase\V2;
use Firebase\V3;

/**
 * Convenience class to comfortably create new Firebase instances.
 *
 * @codeCoverageIgnore
 */
final class Firebase
{
    public static function fromServiceAccount($serviceAccount): V3\Firebase
    {
        return V3\Firebase::fromServiceAccount($serviceAccount);
    }

    public static function fromDatabaseUriAndSecret($databaseUri, string $secret): V2\Firebase
    {
        return V2\Firebase::fromDatabaseUriAndSecret($databaseUri, $secret);
    }
}

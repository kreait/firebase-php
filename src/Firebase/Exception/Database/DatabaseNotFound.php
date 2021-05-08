<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\Database;

use Kreait\Firebase\Exception\DatabaseException;
use LogicException;
use Psr\Http\Message\UriInterface;

final class DatabaseNotFound extends LogicException implements DatabaseException
{
    public static function fromUri(UriInterface $uri): self
    {
        $scheme = $uri->getScheme();
        $host = $uri->getHost();

        $databaseName = \explode('.', $host, 2)[0] ?? '';

        $databaseUri = "{$scheme}://{$host}";
        $suggestedDatabaseUri = \str_replace($databaseName, $databaseName.'-default-rtdb', $databaseUri);

        $message = <<<MESSAGE


            The database at

                {$databaseUri}

            could not be found. You can find the correct name at

                https://console.firebase.google.com/project/_/database

            If you haven't configured the SDK otherwise and if you don't use multiple
            Realtime Databases, the name you will find will most likely be

                {$suggestedDatabaseUri}

            The reason for this is that during the lifetime of the Firebase Admin SDK,
            Firebase has changed the name of the default database. Previously, the default
            database had the same identifier as the project. Since approximately September
            2020, Realtime Databases in newly created projects have the '-default-rtdb'
            suffix.

            For instructions on how to set the name of the used Realtime Database, please
            see https://firebase-php.readthedocs.io/en/5.x/#quick-start


            MESSAGE;

        return new self($message);
    }
}

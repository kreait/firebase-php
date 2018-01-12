<?php

namespace Kreait\Firebase\ServiceAccount\Discovery;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Kreait\Firebase\Exception\ServiceAccountDiscoveryFailed;
use Kreait\Firebase\ServiceAccount;

class FromGoogleWellKnownFile
{
    /**
     * @throws ServiceAccountDiscoveryFailed
     *
     * @return ServiceAccount
     */
    public function __invoke(): ServiceAccount
    {
        $msg = sprintf('%s: The well known file', static::class);

        if (!($credentials = @ServiceAccountCredentials::fromWellKnownFile())) {
            throw new ServiceAccountDiscoveryFailed($msg.' is not readable or invalid');
        }

        // @codeCoverageIgnoreStart
        // We can't really test this because of too many unknowns in the Google library
        /* @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return ServiceAccount::fromValue($credentials);
        // @codeCoverageIgnoreEnd
    }
}

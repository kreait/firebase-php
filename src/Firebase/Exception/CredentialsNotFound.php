<?php

namespace Kreait\Firebase\Exception;

use Kreait\Firebase\Factory;
use Throwable;

/**
 * @deprecated 3.1 Catch \Kreait\Firebase\Exception\ServiceAccountDiscoveryFailed instead
 * @codeCoverageIgnore
 */
class CredentialsNotFound extends ServiceAccountDiscoveryFailed
{
    /**
     * @var string[]
     */
    private $triedPaths;

    public function __construct(array $triedPaths, $message = '', $code = 0, Throwable $previous = null)
    {
        trigger_error(
            sprintf(
                'This exception is deprecated and will be removed in release 4.0 of this library. Catch %s instead.',
                ServiceAccountDiscoveryFailed::class
            ),
            E_USER_DEPRECATED
        );

        $message = $message ?: sprintf(
            'No service account has been found. Tried [%s]. Please set the path to a valid service account credentials file with %s::%s.',
            implode(', ', $triedPaths), Factory::class, 'withCredentials($path)'
        );

        parent::__construct($message, $code, $previous);

        $this->triedPaths = $triedPaths;
    }

    /**
     * @return \string[]
     */
    public function getTriedPaths(): array
    {
        return $this->triedPaths;
    }
}

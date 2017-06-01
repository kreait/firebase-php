<?php

namespace Kreait\Firebase\Exception;

use Kreait\Firebase\Factory;
use Throwable;

class CredentialsNotFound extends LogicException
{
    /**
     * @var string[]
     */
    private $triedPaths;

    public function __construct(array $triedPaths, $message = '', $code = 0, Throwable $previous = null)
    {
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

<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\RemoteConfig;

use Throwable;

class VersionMismatch extends OperationAborted
{
    const IDENTIFER = 'VERSION_MISMATCH';

    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        if (!$message) {
            $message = 'Version mismatch';
        }

        parent::__construct($message, $code, $previous);
    }
}

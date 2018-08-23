<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\RemoteConfig;

use Throwable;

class ValidationFailed extends OperationAborted
{
    const IDENTIFER = 'VALIDATION_ERROR';

    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        if (!$message) {
            $message = 'Validation error';
        }

        parent::__construct($message, $code, $previous);
    }
}

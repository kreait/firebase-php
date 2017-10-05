<?php

namespace Kreait\Firebase\Exception\Auth;

use Kreait\Firebase\Exception\AuthException;
use Throwable;

class MissingPassword extends AuthException
{
    const IDENTIFIER = 'MISSING_PASSWORD';

    public function __construct($code = 0, Throwable $previous = null)
    {
        $message = 'Missing Password: An email user must have a password.';

        parent::__construct($message, $code, $previous);
    }
}

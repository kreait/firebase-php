<?php

namespace Kreait\Firebase\Exception\Auth;

use Kreait\Firebase\Exception\AuthException;
use Throwable;

class InvalidPassword extends AuthException
{
    const IDENTIFIER = 'INVALID_PASSWORD';

    public function __construct($code = 0, Throwable $previous = null)
    {
        $message = 'The password is invalid or the user does not have a password.';

        parent::__construct($message, $code, $previous);
    }
}

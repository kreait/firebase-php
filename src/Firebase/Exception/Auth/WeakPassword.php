<?php

namespace Kreait\Firebase\Exception\Auth;

use Kreait\Firebase\Exception\AuthException;
use Throwable;

class WeakPassword extends AuthException
{
    const IDENTIFIER = 'WEAK_PASSWORD';

    public function __construct($code = 0, Throwable $previous = null)
    {
        $message = 'Weak Password: The password must be 6 characters long or more.';

        parent::__construct($message, $code, $previous);
    }
}

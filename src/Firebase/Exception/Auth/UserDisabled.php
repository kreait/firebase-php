<?php

namespace Kreait\Firebase\Exception\Auth;

use Kreait\Firebase\Exception\AuthException;
use Throwable;

class UserDisabled extends AuthException
{
    const IDENTIFER = 'USER_DISABLED';

    public function __construct($code = 0, Throwable $previous = null)
    {
        $message = 'User disabled: The user account has been disabled by an administrator.';

        parent::__construct($message, $code, $previous);
    }
}

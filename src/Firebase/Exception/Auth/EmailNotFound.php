<?php

namespace Kreait\Firebase\Exception\Auth;

use Kreait\Firebase\Exception\AuthException;
use Throwable;

class EmailNotFound extends AuthException
{
    const IDENTIFIER = 'EMAIL_NOT_FOUND';

    public function __construct($code = 0, Throwable $previous = null)
    {
        $message = 'There is no user record corresponding to this identifier. The user may have been deleted.';

        parent::__construct($message, $code, $previous);
    }
}

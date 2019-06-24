<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\Auth;

use Kreait\Firebase\Exception\AuthException;
use Throwable;

class EmailExists extends AuthException
{
    const IDENTIFIER = 'EMAIL_EXISTS';

    public function __construct($code = 0, Throwable $previous = null)
    {
        $message = 'The email address is already in use by another account.';

        parent::__construct($message, $code, $previous);
    }
}

<?php

namespace Kreait\Firebase\Exception\Auth;

use Kreait\Firebase\Exception\AuthException;
use Throwable;

class CredentialsMismatch extends AuthException
{
    const IDENTIFER = 'CREDENTIALS_MISMATCH';

    public function __construct($code = 0, Throwable $previous = null)
    {
        $message = 'Invalid custom token: The custom token corresponds to a different Firebase project.';

        parent::__construct($message, $code, $previous);
    }
}

<?php

namespace Kreait\Firebase\Exception\Auth;

use Kreait\Firebase\Exception\AuthException;
use Throwable;

class PhoneNumberExists extends AuthException
{
    const IDENTIFIER = 'PHONE_NUMBER_EXISTS';

    public function __construct($code = 0, Throwable $previous = null)
    {
        $message = 'The phone number is already in use by another account.';

        parent::__construct($message, $code, $previous);
    }
}

<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\Auth;

use Kreait\Firebase\Exception\AuthException;
use Throwable;

class InvalidCustomToken extends AuthException
{
    const IDENTIFER = 'INVALID_CUSTOM_TOKEN';

    public function __construct($code = 0, Throwable $previous = null)
    {
        $message = 'Invalid custom token: The custom token format is incorrect or'
            .' the token is invalid for some reason (e.g. expired, invalid signature, etc.)';

        parent::__construct($message, $code, $previous);
    }
}

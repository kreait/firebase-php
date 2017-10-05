<?php

namespace Kreait\Firebase\Exception\Auth;

use Kreait\Firebase\Exception\AuthException;
use Throwable;

class OperationNotAllowed extends AuthException
{
    const IDENTIFER = 'OPERATION_NOT_ALLOWED';

    public function __construct($code = 0, Throwable $previous = null)
    {
        $message = 'Operation not allowed.';

        parent::__construct($message, $code, $previous);
    }
}

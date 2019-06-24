<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\Auth;

use Kreait\Firebase\Exception\AuthException;
use Throwable;

class UserNotFound extends AuthException
{
    const IDENTIFIER = 'USER_NOT_FOUND';

    public function __construct($code = 0, Throwable $previous = null)
    {
        $message = 'User not found: There is no user record corresponding to this identifier. The user may have been deleted.';

        parent::__construct($message, $code, $previous);
    }

    public static function withCustomMessage(string $message): self
    {
        $exception = new self();
        $exception->message = $message;

        return $exception;
    }
}

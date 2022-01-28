<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\Auth;

use Kreait\Firebase\Exception\AuthException;
use Lcobucci\JWT\Token;
use RuntimeException;
use Throwable;

final class RevokedSessionCookie extends RuntimeException implements AuthException
{
    private Token $token;

    public function __construct(Token $token, string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        $message = $message ?: 'The Firebase session cookie has been revoked.';

        parent::__construct($message, $code, $previous);

        $this->token = $token;
    }

    public function getToken(): Token
    {
        return $this->token;
    }
}

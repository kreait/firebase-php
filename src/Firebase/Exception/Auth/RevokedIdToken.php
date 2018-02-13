<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\Auth;

use Kreait\Firebase\Exception\AuthException;
use Lcobucci\JWT\Token;
use Throwable;

class RevokedIdToken extends AuthException
{
    /**
     * @var Token
     */
    private $token;

    public function __construct(Token $token, string $message = '', int $code = 0, Throwable $previous = null)
    {
        $message = $message ?: 'The Firebase ID token has been revoked.';

        parent::__construct($message, $code, $previous);

        $this->token = $token;
    }

    public function getToken(): Token
    {
        // @codeCoverageIgnoreStart
        return $this->token;
        // @codeCoverageIgnoreEnd
    }
}

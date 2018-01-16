<?php

namespace Kreait\Firebase\Exception\Auth;

use Kreait\Firebase\Exception\AuthException;
use Lcobucci\JWT\Token;
use Throwable;

class InvalidIdToken extends AuthException
{
    /**
     * @var Token|mixed
     */
    private $token;

    public function __construct($token, string $message, int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->token = $token;
    }

    public function getToken()
    {
        return $this->token;
    }
}

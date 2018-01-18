<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\Auth;

use Lcobucci\JWT\Token;

class RevokedIdToken extends InvalidIdToken
{
    public function __construct(Token $token)
    {
        parent::__construct($token, 'The Firebase ID token has been revoked.');
    }
}

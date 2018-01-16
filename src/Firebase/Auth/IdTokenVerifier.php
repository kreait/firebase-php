<?php

namespace Kreait\Firebase\Auth;

use Firebase\Auth\Token\Domain\Verifier;
use Kreait\Firebase\Exception\Auth\InvalidIdToken;
use Lcobucci\JWT\Token;

class IdTokenVerifier
{
    /**
     * @var Verifier
     */
    private $verifier;

    public function __construct(Verifier $baseVerifier)
    {
        $this->verifier = $baseVerifier;
    }

    /**
     * @param Token|string $token
     *
     * @throws InvalidIdToken
     *
     * @return Token
     */
    public function verify($token): Token
    {
        try {
            return $this->verifier->verifyIdToken($token);
        } catch (\Throwable $e) {
            throw new InvalidIdToken($token, $e->getMessage());
        }
    }
}

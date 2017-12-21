<?php

namespace Kreait\Firebase\Auth;

use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Token;

class User
{
    /**
     * @var Token
     */
    private $idToken;

    /**
     * @var string
     */
    private $refreshToken;

    public static function create($idToken = null, string $refreshToken = null): self
    {
        if (!($idToken instanceof Token)) {
            $idToken = (new Parser())->parse($idToken);
        }

        $user = new static();
        $user->setIdToken($idToken);
        $user->setRefreshToken($refreshToken);

        return $user;
    }

    /**
     * @internal
     *
     * @param Token $token
     */
    public function setIdToken(Token $token)
    {
        $this->idToken = $token;
    }

    /**
     * @internal
     *
     * @param string $refreshToken
     */
    public function setRefreshToken(string $refreshToken)
    {
        $this->refreshToken = $refreshToken;
    }

    public function getUid(): string
    {
        /* @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return (string) $this->idToken->getClaim('user_id');
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        try {
            return (string) $this->idToken->getClaim('email');
        } catch (\OutOfBoundsException $e) {
            return null;
        }
    }

    public function hasVerifiedEmail(): bool
    {
        return (bool) $this->idToken->getClaim('email_verified', false);
    }

    /**
     * @return Token
     */
    public function getIdToken(): Token
    {
        return $this->idToken;
    }

    /**
     * @return string
     */
    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }
}

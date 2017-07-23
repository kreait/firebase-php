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
        $idToken = $idToken instanceof Token ?: (new Parser())->parse($idToken);

        $user = new static();
        $user->setIdToken($idToken);
        $user->setRefreshToken($refreshToken);

        return $user;
    }

    public function setIdToken(Token $token)
    {
        $this->idToken = $token;
    }

    public function setRefreshToken(string $refreshToken)
    {
        $this->refreshToken = $refreshToken;
    }

    /**
     * @return string
     */
    public function getUid(): string
    {
        /* @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return (string) $this->idToken->getClaim('user_id');
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

<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

final class SignInWithRefreshToken implements SignIn
{
    /** @var string */
    private $refreshToken;

    private function __construct()
    {
    }

    public static function fromValue(string $refreshToken): self
    {
        $instance = new self();
        $instance->refreshToken = $refreshToken;

        return $instance;
    }

    public function refreshToken(): string
    {
        return $this->refreshToken;
    }
}

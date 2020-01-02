<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

final class SignInAnonymously implements SignIn
{
    private function __construct()
    {
    }

    public static function new(): self
    {
        return new self();
    }
}

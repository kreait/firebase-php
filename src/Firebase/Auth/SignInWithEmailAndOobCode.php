<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

final class SignInWithEmailAndOobCode implements SignIn
{
    /** @var string */
    private $email;

    /** @var string */
    private $oobCode;

    private function __construct()
    {
    }

    public static function fromValues(string $email, string $oobCode): self
    {
        $instance = new self();
        $instance->email = $email;
        $instance->oobCode = $oobCode;

        return $instance;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function oobCode(): string
    {
        return $this->oobCode;
    }
}

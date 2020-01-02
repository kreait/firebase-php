<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

final class SignInWithEmailAndPassword implements SignIn
{
    /** @var string */
    private $email;

    /** @var string */
    private $clearTextPassword;

    private function __construct()
    {
    }

    public static function fromValues(string $email, string $clearTextPassword): self
    {
        $instance = new self();
        $instance->email = $email;
        $instance->clearTextPassword = $clearTextPassword;

        return $instance;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function clearTextPassword(): string
    {
        return $this->clearTextPassword;
    }
}

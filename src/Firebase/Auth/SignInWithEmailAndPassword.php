<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

/**
 * @internal
 */
final class SignInWithEmailAndPassword implements IsTenantAware, SignIn
{
    private string $email;
    private string $clearTextPassword;
    private ?string $tenantId = null;

    private function __construct(string $email, string $clearTextPassword)
    {
        $this->email = $email;
        $this->clearTextPassword = $clearTextPassword;
    }

    public static function fromValues(string $email, string $clearTextPassword): self
    {
        return new self($email, $clearTextPassword);
    }

    public function email(): string
    {
        return $this->email;
    }

    public function clearTextPassword(): string
    {
        return $this->clearTextPassword;
    }

    public function withTenantId(string $tenantId): self
    {
        $action = clone $this;
        $action->tenantId = $tenantId;

        return $action;
    }

    public function tenantId(): ?string
    {
        return $this->tenantId;
    }
}

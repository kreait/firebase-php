<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

/**
 * @internal
 */
final class SignInWithEmailAndOobCode implements IsTenantAware, SignIn
{
    private string $email;
    private string $oobCode;
    private ?string $tenantId = null;

    private function __construct(string $email, string $oobCode)
    {
        $this->email = $email;
        $this->oobCode = $oobCode;
    }

    public static function fromValues(string $email, string $oobCode): self
    {
        return new self($email, $oobCode);
    }

    public function withTenantId(string $tenantId): self
    {
        $action = clone $this;
        $action->tenantId = $tenantId;

        return $action;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function oobCode(): string
    {
        return $this->oobCode;
    }

    public function tenantId(): ?string
    {
        return $this->tenantId;
    }
}

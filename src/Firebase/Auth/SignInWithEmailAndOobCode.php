<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

final class SignInWithEmailAndOobCode implements IsTenantAware, SignIn
{
    /** @var string */
    private $email;

    /** @var string */
    private $oobCode;

    /** @var TenantId|null */
    private $tenantId;

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

    public function withTenantId(TenantId $tenantId): self
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

    public function tenantId(): ?TenantId
    {
        return $this->tenantId;
    }
}

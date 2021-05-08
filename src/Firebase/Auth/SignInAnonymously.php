<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

final class SignInAnonymously implements SignIn
{
    private ?TenantId $tenantId = null;

    private function __construct()
    {
    }

    public static function new(): self
    {
        return new self();
    }

    public function withTenantId(TenantId $tenantId): self
    {
        $action = clone $this;
        $action->tenantId = $tenantId;

        return $action;
    }

    public function tenantId(): ?TenantId
    {
        return $this->tenantId;
    }
}

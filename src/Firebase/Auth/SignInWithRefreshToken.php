<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

final class SignInWithRefreshToken implements IsTenantAware, SignIn
{
    private string $refreshToken;
    private ?TenantId $tenantId = null;

    private function __construct(string $refreshToken)
    {
        $this->refreshToken = $refreshToken;
    }

    public static function fromValue(string $refreshToken): self
    {
        return new self($refreshToken);
    }

    public function withTenantId(TenantId $tenantId): self
    {
        $action = clone $this;
        $action->tenantId = $tenantId;

        return $action;
    }

    public function refreshToken(): string
    {
        return $this->refreshToken;
    }

    public function tenantId(): ?TenantId
    {
        return $this->tenantId;
    }
}

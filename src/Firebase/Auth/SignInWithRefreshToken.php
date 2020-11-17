<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

final class SignInWithRefreshToken implements IsTenantAware, SignIn
{
    /** @var string */
    private $refreshToken;

    /** @var TenantId|null */
    private $tenantId;

    private function __construct()
    {
    }

    public static function fromValue(string $refreshToken): self
    {
        $instance = new self();
        $instance->refreshToken = $refreshToken;

        return $instance;
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

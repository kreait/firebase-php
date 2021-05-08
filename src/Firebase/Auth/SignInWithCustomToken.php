<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

final class SignInWithCustomToken implements IsTenantAware, SignIn
{
    private string $customToken;

    private ?TenantId $tenantId = null;

    private function __construct(string $customToken)
    {
        $this->customToken = $customToken;
    }

    public static function fromValue(string $customToken): self
    {
        return new self($customToken);
    }

    public function withTenantId(TenantId $tenantId): self
    {
        $action = clone $this;
        $action->tenantId = $tenantId;

        return $action;
    }

    public function customToken(): string
    {
        return $this->customToken;
    }

    public function tenantId(): ?TenantId
    {
        return $this->tenantId;
    }
}

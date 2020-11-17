<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

final class SignInWithCustomToken implements IsTenantAware, SignIn
{
    /** @var string */
    private $customToken;

    /** @var TenantId|null */
    private $tenantId;

    private function __construct()
    {
    }

    public static function fromValue(string $customToken): self
    {
        $instance = new self();
        $instance->customToken = $customToken;

        return $instance;
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

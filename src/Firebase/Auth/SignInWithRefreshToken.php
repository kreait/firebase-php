<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

/**
 * @internal
 */
final class SignInWithRefreshToken implements IsTenantAware, SignIn
{
    private string $refreshToken;
    private ?string $tenantId = null;

    private function __construct(string $refreshToken)
    {
        $this->refreshToken = $refreshToken;
    }

    public static function fromValue(string $refreshToken): self
    {
        return new self($refreshToken);
    }

    public function withTenantId(string $tenantId): self
    {
        $action = clone $this;
        $action->tenantId = $tenantId;

        return $action;
    }

    public function refreshToken(): string
    {
        return $this->refreshToken;
    }

    public function tenantId(): ?string
    {
        return $this->tenantId;
    }
}

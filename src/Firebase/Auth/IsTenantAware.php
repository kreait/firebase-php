<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

interface IsTenantAware
{
    public function tenantId(): ?TenantId;
}

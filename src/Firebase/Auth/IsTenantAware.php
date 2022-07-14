<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

/**
 * @internal
 */
interface IsTenantAware
{
    public function tenantId(): ?string;
}

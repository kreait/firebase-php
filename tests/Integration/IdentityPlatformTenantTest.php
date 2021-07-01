<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Kreait\Firebase\Contract\IdentityPlatform;

/**
 * @internal
 */
class IdentityPlatformTenantTest extends IdentityPlatformTest
{
    protected function setupIdentityPlatform(): IdentityPlatform
    {
        return self::$factory->withTenantId(self::TENANT_ID)->createIdentityPlatform();
    }
}

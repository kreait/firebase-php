<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('emulator')]
final class AuthTest extends AuthTestCase
{
    protected function setUp(): void
    {
        $this->auth = self::$factory->createAuth();
    }
}

<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

/**
 * @internal
 *
 * @group emulator
 */
final class AuthTest extends AuthTestCase
{
    protected function setUp(): void
    {
        $this->auth = self::$factory->createAuth();
    }
}

<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

/**
 * @internal
 */
final class AuthTest extends AuthTestCase
{
    protected function setUp(): void
    {
        $this->auth = self::$factory->createAuth();
    }
}

<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Kreait\Firebase\Exception\Database\DatabaseNotFound;
use Kreait\Firebase\Factory;

/**
 * @internal
 *
 * @group database-emulator
 * @group emulator
 */
final class DatabaseTest extends DatabaseTestCase
{
    public function testWithNonExistingDatabase(): void
    {
        if (self::databaseIsEmulated()) {
            $this->markTestSkipped('The RTDB emulator creates databases if they don\'t exist');
        }

        $credentials = self::$serviceAccountAsArray;
        $credentials['project_id'] = 'non-existing';

        $this->expectException(DatabaseNotFound::class);

        (new Factory())
            ->withServiceAccount($credentials)
            ->createDatabase()
            ->getRuleSet();
    }
}

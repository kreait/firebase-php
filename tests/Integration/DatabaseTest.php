<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Kreait\Firebase\Exception\Database\DatabaseNotFound;
use Kreait\Firebase\Factory;

/**
 * @internal
 */
class DatabaseTest extends DatabaseTestCase
{
    public function testWithSanitizableProjectId(): void
    {
        $credentials = self::$serviceAccount->asArray();
        $credentials['project_id'] = \str_replace('-&+§', ':', $credentials['project_id']);

        (new Factory())
            ->withServiceAccount($credentials)
            ->createDatabase()
            ->getRuleSet();
        $this->addToAssertionCount(1);
    }

    public function testWithNonExistingDatabase(): void
    {
        $credentials = self::$serviceAccount->asArray();
        $credentials['project_id'] .= 'non-existing';

        $this->expectException(DatabaseNotFound::class);

        (new Factory())
            ->withServiceAccount($credentials)
            ->createDatabase()
            ->getRuleSet();
    }
}

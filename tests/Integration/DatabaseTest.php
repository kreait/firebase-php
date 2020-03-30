<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

/**
 * @internal
 */
class DatabaseTest extends DatabaseTestCase
{
    public function testWithSanitizableProjectId(): void
    {
        if (!self::$serviceAccount) {
            $this->markTestSkipped('The integration tests require credentials');
        }

        $credentials = self::$serviceAccount->asArray();
        $credentials['project_id'] = \str_replace('-&+§', ':', $credentials['project_id']);

        $serviceAccount = ServiceAccount::fromValue($credentials);
        (new Factory())
            ->withServiceAccount($serviceAccount)
            ->createDatabase()
            ->getRuleSet();
        $this->addToAssertionCount(1);
    }
}

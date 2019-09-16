<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Util\JSON;

/**
 * @internal
 */
class DatabaseTest extends DatabaseTestCase
{
    public function testWithSanitizableProjectId()
    {
        $credentialsPath = self::$fixturesDir.'/test_credentials.json';

        if (!\file_exists($credentialsPath)) {
            $this->markTestSkipped();
        }

        $credentials = JSON::decode(\file_get_contents($credentialsPath), true);
        $credentials['project_id'] = \str_replace('-&+§', ':', $credentials['project_id']);

        $serviceAccount = ServiceAccount::fromArray($credentials);
        (new Factory())
            ->withServiceAccount($serviceAccount)
            ->createDatabase()
            ->getRuleSet();
        $this->addToAssertionCount(1);
    }
}

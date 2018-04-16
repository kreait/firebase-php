<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests;

use Kreait\Firebase;
use Kreait\Firebase\ServiceAccount;

abstract class IntegrationTestCase extends FirebaseTestCase
{
    /**
     * @var Firebase
     */
    protected static $firebase;

    /**
     * @var ServiceAccount
     */
    protected static $serviceAccount;

    public static function setUpBeforeClass()
    {
        $credentialsPath = self::$fixturesDir.'/test_credentials.json';

        if (!file_exists($credentialsPath)) {
            self::markTestSkipped();
        }

        try {
            self::$serviceAccount = ServiceAccount::fromJsonFile($credentialsPath);
        } catch (\Throwable $e) {
            self::markTestSkipped('The integration tests require a credentials file at "'.$credentialsPath.'"."');

            return;
        }

        self::$firebase = (new Firebase\Factory())
            ->withServiceAccount(self::$serviceAccount)
            ->create();
    }
}

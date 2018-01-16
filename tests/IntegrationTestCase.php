<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests;

use Kreait\Firebase;

abstract class IntegrationTestCase extends FirebaseTestCase
{
    /**
     * @var Firebase
     */
    protected static $firebase;

    public static function setUpBeforeClass()
    {
        $credentialsPath = self::$fixturesDir.'/test_credentials.json';

        try {
            $serviceAccount = Firebase\ServiceAccount::fromJsonFile($credentialsPath);
        } catch (\Throwable $e) {
            self::markTestSkipped('The integration tests require a credentials file at "'.$credentialsPath.'"."');

            return;
        }

        self::$firebase = (new Firebase\Factory())
            ->withServiceAccount($serviceAccount)
            ->create();
    }
}

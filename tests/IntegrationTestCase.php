<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests;

use Kreait\Firebase;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Util\JSON;

abstract class IntegrationTestCase extends FirebaseTestCase
{
    /**
     * @var Firebase
     */
    protected static $firebase;

    /**
     * @var Factory
     */
    protected static $factory;

    /**
     * @var ServiceAccount
     */
    protected static $serviceAccount;

    /**
     * @var string[]
     */
    protected static $registrationTokens = [];

    public static function setUpBeforeClass()
    {
        if (file_exists($testDevices = self::$fixturesDir.'/test_devices.json')) {
            self::$registrationTokens = JSON::decode(file_get_contents($testDevices), true);
        }

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

        self::$factory = (new Factory())->withServiceAccount(self::$serviceAccount);

        self::$firebase = self::$factory->create();
    }
}

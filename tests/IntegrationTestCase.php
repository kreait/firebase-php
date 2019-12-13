<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests;

use Kreait\Firebase\Auth\UserRecord;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Request\CreateUser;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Util\JSON;
use Throwable;

abstract class IntegrationTestCase extends FirebaseTestCase
{
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
        if (\file_exists($testDevices = self::$fixturesDir.'/test_devices.json')) {
            self::$registrationTokens = JSON::decode((string) \file_get_contents($testDevices), true);
        }

        $credentialsPath = self::$fixturesDir.'/test_credentials.json';

        if (!\file_exists($credentialsPath)) {
            self::markTestSkipped();
        }

        try {
            self::$serviceAccount = ServiceAccount::fromJsonFile($credentialsPath);
        } catch (Throwable $e) {
            self::markTestSkipped('The integration tests require a credentials file at "'.$credentialsPath.'"."');

            return;
        }

        self::$factory = (new Factory())->withServiceAccount(self::$serviceAccount);
    }

    protected function createUserWithEmailAndPassword(string $email = null, string $password = null): UserRecord
    {
        /** @noinspection NonSecureUniqidUsageInspection */
        $uniqid = \uniqid();
        $email = $email ?? "{$uniqid}@domain.tld";
        $password = $password ?? $uniqid;

        return self::$factory
            ->createAuth()
            ->createUser(
                CreateUser::new()
                    ->withUnverifiedEmail($email)
                    ->withClearTextPassword($password)
            );
    }

    protected function deleteUser($userOrUid)
    {
        $uid = $userOrUid instanceof UserRecord ? $userOrUid->uid : $userOrUid;

        self::$factory->createAuth()->deleteUser($uid);
    }
}

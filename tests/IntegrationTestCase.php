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
     * @var ServiceAccount|null
     */
    protected static $serviceAccount;

    /**
     * @var string[]
     */
    protected static $registrationTokens = [];

    public static function setUpBeforeClass()
    {
        self::$serviceAccount = self::credentialsFromEnvironment() ?? self::credentialsFromFile();

        if (!self::$serviceAccount) {
            self::markTestSkipped('The integration tests require credentials');
        }

        self::$factory = (new Factory())->withServiceAccount(self::$serviceAccount);

        self::$registrationTokens = self::registrationTokensFromEnvironment() ?? self::registrationTokensFromFile() ?? [];
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

    /**
     * @return ServiceAccount|null
     */
    private static function credentialsFromFile()
    {
        $credentialsPath = self::$fixturesDir.'/test_credentials.json';

        if (!\file_exists($credentialsPath)) {
            return null;
        }

        try {
            return ServiceAccount::fromJsonFile($credentialsPath);
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * @return ServiceAccount|null
     */
    private static function credentialsFromEnvironment()
    {
        if ($credentials = getenv('TEST_FIREBASE_CREDENTIALS')) {
            return ServiceAccount::fromValue($credentials);
        }

        return null;
    }

    /**
     * @return array|null
     */
    private static function registrationTokensFromFile()
    {
        $path = self::$fixturesDir.'/test_devices.json';

        if (!\file_exists($path)) {
            return null;
        }

        try {
            if ($contents = file_get_contents($path)) {
                return JSON::decode($contents, true);
            }

            return null;
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * @return array|null
     */
    private static function registrationTokensFromEnvironment()
    {
        if (!($tokens = getenv('TEST_REGISTRATION_TOKENS'))) {
            return null;
        }

        try {
            return JSON::decode($tokens, true);
        } catch (Throwable $e) {
            return null;
        }
    }
}

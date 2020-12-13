<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests;

use Kreait\Firebase\Auth\UserRecord;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Util;
use Kreait\Firebase\Util\JSON;
use Kreait\Firebase\Value\Uid;
use Throwable;

abstract class IntegrationTestCase extends FirebaseTestCase
{
    /** @var Factory */
    protected static $factory;

    /** @var ServiceAccount|null */
    protected static $serviceAccount;

    /** @var string[] */
    protected static $registrationTokens = [];

    public static function setUpBeforeClass(): void
    {
        self::$serviceAccount = self::credentialsFromEnvironment() ?? self::credentialsFromFile();

        if (!self::$serviceAccount) {
            self::markTestSkipped('The integration tests require credentials');
        }

        self::$factory = (new Factory())->withServiceAccount(self::$serviceAccount);

        self::$registrationTokens = self::registrationTokensFromEnvironment() ?? self::registrationTokensFromFile() ?? [];
    }

    protected function createUserWithEmailAndPassword(?string $email = null, ?string $password = null): UserRecord
    {
        $email = $email ?? self::randomEmail();
        $password = $password ?? self::randomString();

        return self::$factory
            ->createAuth()
            ->createUser([
                'email' => $email,
                'clear_text_password' => $password,
            ]);
    }

    /**
     * @param UserRecord|Uid|string $userOrUid
     */
    protected function deleteUser($userOrUid): void
    {
        $uid = $userOrUid instanceof UserRecord ? $userOrUid->uid : $userOrUid;

        try {
            self::$factory->createAuth()->deleteUser($uid);
        } catch (Throwable $e) {
            // Well, if that failed, *we're* failed
        }
    }

    protected function getTestRegistrationToken(): string
    {
        if (empty(self::$registrationTokens)) {
            $this->markTestSkipped('No registration token available');
        }

        /* @noinspection NonSecureArrayRandUsageInspection */
        return self::$registrationTokens[\array_rand(self::$registrationTokens)];
    }

    protected static function randomString(string $suffix = ''): string
    {
        return \mb_strtolower(\bin2hex(\random_bytes(5)).$suffix);
    }

    protected static function randomEmail(string $suffix = ''): string
    {
        return self::randomString($suffix.'@domain.tld');
    }

    private static function credentialsFromFile(): ?ServiceAccount
    {
        $credentialsPath = self::$fixturesDir.'/test_credentials.json';

        if (!\file_exists($credentialsPath)) {
            return null;
        }

        try {
            return ServiceAccount::fromValue($credentialsPath);
        } catch (Throwable $e) {
            return null;
        }
    }

    private static function credentialsFromEnvironment(): ?ServiceAccount
    {
        if ($credentials = Util::getenv('TEST_FIREBASE_CREDENTIALS')) {
            return ServiceAccount::fromValue($credentials);
        }

        return null;
    }

    private static function registrationTokensFromFile(): ?array
    {
        $path = self::$fixturesDir.'/test_devices.json';

        if (!\file_exists($path)) {
            return null;
        }

        try {
            if ($contents = \file_get_contents($path)) {
                return JSON::decode($contents, true);
            }

            return null;
        } catch (Throwable $e) {
            return null;
        }
    }

    private static function registrationTokensFromEnvironment(): ?array
    {
        if (!($tokens = Util::getenv('TEST_REGISTRATION_TOKENS'))) {
            return null;
        }

        try {
            return JSON::decode($tokens, true);
        } catch (Throwable $e) {
            return null;
        }
    }
}

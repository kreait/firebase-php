<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests;

use Beste\Json;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Util;
use Throwable;

abstract class IntegrationTestCase extends FirebaseTestCase
{
    public const TENANT_ID = 'Test-bs38l';

    protected static Factory $factory;

    protected static ServiceAccount $serviceAccount;
    protected static string $rtdbUrl;

    /** @var string[] */
    protected static array $registrationTokens = [];

    protected static string $unknownToken = 'd_RTtLHR_JgI4r4tbYM9CA:APA91bEzb2Tb3WlKwddpEPYY2ZAx7AOmjOhiw-jVq6J9ekJGpBAefAgMb1muDJcKBMsrMq7zSCfBzl0Ll7JCZ0o8QI9zLVG1F18nqW9AOFKDXi8-MyT3R5Stt6GGKnq9rd9l5kopGEbO';

    public static function setUpBeforeClass(): void
    {
        $credentials = self::credentialsFromEnvironment() ?? self::credentialsFromFile();

        if (!$credentials instanceof ServiceAccount) {
            self::markTestSkipped('The integration tests require credentials');
        }

        self::$serviceAccount = $credentials;
        self::$rtdbUrl = self::rtdbUrlFromEnvironment() ?? self::rtdbUrlFromFile() ?? '';

        self::$factory = (new Factory())
            ->withServiceAccount(self::$serviceAccount)
            ->withDatabaseUri(self::$rtdbUrl)
        ;

        self::$registrationTokens = self::registrationTokensFromEnvironment() ?? self::registrationTokensFromFile() ?? [];
    }

    protected function getTestRegistrationToken(): string
    {
        if (empty(self::$registrationTokens)) {
            $this->markTestSkipped('No registration token available');
        }

        // @noinspection NonSecureArrayRandUsageInspection
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

    protected static function isEmulated(): bool
    {
        return !\in_array(Util::getenv('FIREBASE_AUTH_EMULATOR_HOST'), [null, ''], true);
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
        if ($credentials = Util::getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
            return ServiceAccount::fromValue($credentials);
        }

        return null;
    }

    /**
     * @return string[]|null
     */
    private static function registrationTokensFromFile(): ?array
    {
        $path = self::$fixturesDir.'/test_devices.json';

        if (!\file_exists($path)) {
            return null;
        }

        try {
            if ($contents = \file_get_contents($path)) {
                return Json::decode($contents, true);
            }

            return null;
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * @return string[]|null
     */
    private static function registrationTokensFromEnvironment(): ?array
    {
        if (!($tokens = Util::getenv('TEST_REGISTRATION_TOKENS'))) {
            return null;
        }

        try {
            return Json::decode($tokens, true);
        } catch (Throwable $e) {
            return null;
        }
    }

    private static function rtdbUrlFromFile(): ?string
    {
        $path = self::$fixturesDir.'/test_rtdb.json';

        if (!\file_exists($path)) {
            return null;
        }

        try {
            if ($contents = \file_get_contents($path)) {
                return Json::decode($contents, true);
            }

            return null;
        } catch (Throwable $e) {
            return null;
        }
    }

    private static function rtdbUrlFromEnvironment(): ?string
    {
        return Util::getenv('TEST_FIREBASE_RTDB_URI');
    }
}

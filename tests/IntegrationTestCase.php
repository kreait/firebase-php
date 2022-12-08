<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests;

use Beste\Json;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Util;
use Throwable;

use function array_rand;
use function bin2hex;
use function file_exists;
use function file_get_contents;
use function is_array;
use function mb_strtolower;
use function random_bytes;

/**
 * @internal
 *
 * @phpstan-import-type ServiceAccountShape from Factory
 */
abstract class IntegrationTestCase extends FirebaseTestCase
{
    protected static Factory $factory;

    /** @var ServiceAccountShape */
    protected static array $serviceAccount;

    /** @var non-empty-string|null */
    protected static ?string $rtdbUrl;

    /** @var non-empty-string|null */
    protected static ?string $tenantId;

    /** @var list<string> */
    protected static array $registrationTokens = [];
    protected static string $unknownToken = 'd_RTtLHR_JgI4r4tbYM9CA:APA91bEzb2Tb3WlKwddpEPYY2ZAx7AOmjOhiw-jVq6J9ekJGpBAefAgMb1muDJcKBMsrMq7zSCfBzl0Ll7JCZ0o8QI9zLVG1F18nqW9AOFKDXi8-MyT3R5Stt6GGKnq9rd9l5kopGEbO';

    public static function setUpBeforeClass(): void
    {
        $credentials = self::credentialsFromEnvironment() ?? self::credentialsFromFile();

        if (!$credentials) {
            self::markTestSkipped('The integration tests require credentials');
        }

        if (str_starts_with($credentials, '{')) {
            self::$serviceAccount = Json::decode($credentials, true);
        } else {
            self::$serviceAccount = Json::decodeFile($credentials, true);
        }

        self::$factory = (new Factory())->withServiceAccount(self::$serviceAccount);
        self::$registrationTokens = self::registrationTokens();
        self::$rtdbUrl = self::rtdbUrl();
        self::$tenantId = self::tenantId();
    }

    protected function getTestRegistrationToken(): string
    {
        if (empty(self::$registrationTokens)) {
            $this->markTestSkipped('No registration token available');
        }

        // @noinspection NonSecureArrayRandUsageInspection
        return self::$registrationTokens[array_rand(self::$registrationTokens)];
    }

    /**
     * @return non-empty-string
     */
    protected static function randomString(string $suffix = ''): string
    {
        return mb_strtolower(bin2hex(random_bytes(5)).$suffix);
    }

    /**
     * @return non-empty-string
     */
    protected static function randomEmail(string $suffix = ''): string
    {
        return self::randomString($suffix.'@domain.tld');
    }

    protected static function authIsEmulated(): bool
    {
        return Util::authEmulatorHost() !== null;
    }

    protected static function databaseIsEmulated(): bool
    {
        return Util::rtdbEmulatorHost() !== null;
    }

    /**
     * @return non-empty-string|null
     */
    private static function credentialsFromFile(): ?string
    {
        $credentialsPath = self::$fixturesDir.'/test_credentials.json';

        return file_exists($credentialsPath)
            ? $credentialsPath
            : null;
    }

    /**
     * @return non-empty-string|null
     */
    private static function credentialsFromEnvironment(): ?string
    {
        return ($credentials = Util::getenv('GOOGLE_APPLICATION_CREDENTIALS'))
            ? $credentials
            : null;
    }

    /**
     * @return list<non-empty-string>
     */
    private static function registrationTokens(): array
    {
        $registrationTokens = self::registrationTokensFromEnvironment() ?? self::registrationTokensFromFile();
        $registrationTokens = array_map(trim(...), $registrationTokens);
        $registrationTokens = array_filter($registrationTokens);

        return array_values($registrationTokens);
    }

    /**
     * @return array<string>
     */
    private static function registrationTokensFromFile(): array
    {
        $tokens = [];

        $path = self::$fixturesDir.'/test_devices.json';

        if (!file_exists($path)) {
            return $tokens;
        }

        try {
            if ($contents = file_get_contents($path)) {
                $tokens = Json::decode($contents, true);
            }
        } catch (Throwable) {
        }

        return array_map(strval(...), $tokens);
    }

    /**
     * @return array<string>|null
     */
    private static function registrationTokensFromEnvironment(): ?array
    {
        if (!($tokens = Util::getenv('TEST_REGISTRATION_TOKENS'))) {
            return null;
        }

        try {
            $tokens = Json::decode($tokens, true);
        } catch (Throwable) {
            return null;
        }

        if (!is_array($tokens)) {
            return null;
        }

        return array_map(strval(...), $tokens);
    }

    /**
     * @return non-empty-string|null
     */
    private static function rtdbUrl(): ?string
    {
        return self::setting('TEST_FIREBASE_RTDB_URI', 'test_rtdb.json');
    }

    /**
     * @return non-empty-string|null
     */
    private static function tenantId(): ?string
    {
        return self::setting('TEST_FIREBASE_TENANT_ID', 'test_tenant.json');
    }

    /**
     * @return non-empty-string|null
     */
    private static function setting(string $envName, string $envFile): ?string
    {
        return self::settingFromEnv($envName) ?? self::settingFromFile($envFile);
    }

    /**
     * @return non-empty-string|null
     */
    private static function settingFromFile(string $envFile): ?string
    {
        $path = self::$fixturesDir.'/'.$envFile;

        if (!file_exists($path)) {
            return null;
        }

        try {
            if ($contents = file_get_contents($path)) {
                return Json::decode($contents, true);
            }

            return null;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @return non-empty-string|null
     */
    private static function settingFromEnv(string $envKey): ?string
    {
        return Util::getenv($envKey);
    }
}

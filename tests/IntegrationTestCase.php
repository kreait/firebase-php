<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests;

use Beste\Json;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Util;

use function array_rand;
use function bin2hex;
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

    /**
     * @var ServiceAccountShape
     */
    protected static array $serviceAccount;

    /**
     * @var non-empty-string|null
     */
    protected static ?string $rtdbUrl;

    /**
     * @var non-empty-string|null
     */
    protected static ?string $tenantId;

    /**
     * @var non-empty-string|null
     */
    protected static ?string $appId;

    /**
     * @var list<non-empty-string>
     */
    protected static array $registrationTokens = [];
    protected static string $unknownToken = 'd_RTtLHR_JgI4r4tbYM9CA:APA91bEzb2Tb3WlKwddpEPYY2ZAx7AOmjOhiw-jVq6J9ekJGpBAefAgMb1muDJcKBMsrMq7zSCfBzl0Ll7JCZ0o8QI9zLVG1F18nqW9AOFKDXi8-MyT3R5Stt6GGKnq9rd9l5kopGEbO';

    public static function setUpBeforeClass(): void
    {
        $credentials = self::credentialsFromEnvironment();

        if (!$credentials) {
            self::markTestSkipped('The integration tests require credentials');
        }

        self::$serviceAccount = Json::decode($credentials, true);

        self::$factory = (new Factory())->withServiceAccount(self::$serviceAccount);
        self::$registrationTokens = self::registrationTokensFromEnvironment();
        self::$rtdbUrl = Util::getenv('TEST_FIREBASE_RTDB_URI');
        self::$tenantId = Util::getenv('TEST_FIREBASE_TENANT_ID');
        self::$appId = Util::getenv('TEST_FIREBASE_APP_ID');
    }

    /**
     * @return non-empty-string
     */
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
        return self::randomString($suffix.'@example.com');
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
    private static function registrationTokensFromEnvironment(): array
    {
        $tokens = Json::decode(Util::getenv('TEST_REGISTRATION_TOKENS') ?? '', true);
        $tokens = array_map(strval(...), $tokens);
        $tokens = array_map(trim(...), $tokens);
        $tokens = array_filter($tokens, fn($token): bool => $token !== '');

        return array_values($tokens);
    }
}

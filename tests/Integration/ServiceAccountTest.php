<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Beste\Json;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Util;
use PHPUnit\Framework\TestCase;

use function assert;

/**
 * @internal
 */
final class ServiceAccountTest extends TestCase
{
    /** @var non-empty-string */
    private static string $credentialsPath;
    private static bool $credentialsPathIsTemporary = false;

    public static function setUpBeforeClass(): void
    {
        $credentialsFromEnvironment = Util::getenv('GOOGLE_APPLICATION_CREDENTIALS');

        if ($credentialsFromEnvironment !== null && str_starts_with($credentialsFromEnvironment, '{')) {
            // Don't overwrite the fixtures file
            $credentialsPath = __DIR__.'/test_credentials.json';
            self::$credentialsPathIsTemporary = true;

            $result = file_put_contents($credentialsPath, $credentialsFromEnvironment);

            if ($result === false) {
                self::fail("Unable to write credentials to file `{$credentialsPath}`");
            }

            Util::putenv('GOOGLE_APPLICATION_CREDENTIALS', $credentialsPath);
        } elseif (!file_exists($credentialsPath = __DIR__.'/../_fixtures/test_credentials.json')) {
            self::markTestSkipped('The integration tests require credentials');
        }

        self::$credentialsPath = $credentialsPath;
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$credentialsPathIsTemporary) {
            unlink(self::$credentialsPath);
        }
    }

    public function testWithPathToServiceAccount(): void
    {
        $factory = (new Factory())->withServiceAccount(self::$credentialsPath);

        $this->assertFunctioningConnection($factory);
    }

    public function testWithJsonString(): void
    {
        $json = file_get_contents(self::$credentialsPath);
        assert($json !== false && $json !== '');

        $factory = (new Factory())->withServiceAccount($json);

        $this->assertFunctioningConnection($factory);
    }

    public function testWithArray(): void
    {
        $json = file_get_contents(self::$credentialsPath);
        assert($json !== false && $json !== '');

        $array = Json::decode($json, true);

        $factory = (new Factory())->withServiceAccount($array);

        $this->assertFunctioningConnection($factory);
    }

    public function testWithGoogleApplicationCredentialsAsFilePath(): void
    {
        Util::putenv('GOOGLE_APPLICATION_CREDENTIALS', self::$credentialsPath);

        $this->assertFunctioningConnection(new Factory());
    }

    public function testWithGoogleApplicationCredentialsAsJsonString(): void
    {
        $json = file_get_contents(self::$credentialsPath);
        assert($json !== false && $json !== '');

        Util::putenv('GOOGLE_APPLICATION_CREDENTIALS', $json);

        $this->assertFunctioningConnection(new Factory());
    }

    private function assertFunctioningConnection(Factory $factory): void
    {
        $auth = $factory->createAuth();
        $user = null;

        try {
            $user = $auth->createAnonymousUser();
            $this->addToAssertionCount(1);
        } finally {
            if ($user !== null) {
                $auth->deleteUser($user->uid);
            }
        }
    }
}

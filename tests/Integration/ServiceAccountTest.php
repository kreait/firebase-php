<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Tests\IntegrationTestCase;
use Kreait\Firebase\Util;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Test;

use function assert;

/**
 * @internal
 */
final class ServiceAccountTest extends IntegrationTestCase
{
    /**
     * @var non-empty-string
     */
    private static string $credentialsPath;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$credentialsPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'test_credentials.json';
        file_put_contents(self::$credentialsPath, json_encode(self::$serviceAccount));
        Util::putenv('GOOGLE_APPLICATION_CREDENTIALS', self::$credentialsPath);
    }

    public static function tearDownAfterClass(): void
    {
        unlink(self::$credentialsPath);
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function withPathToServiceAccount(): void
    {
        $factory = (new Factory())->withServiceAccount(self::$credentialsPath);

        $this->assertFunctioningConnection($factory);
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function withJsonString(): void
    {
        $json = file_get_contents(self::$credentialsPath);
        assert($json !== false && $json !== '');

        $factory = (new Factory())->withServiceAccount($json);

        $this->assertFunctioningConnection($factory);
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function withArray(): void
    {
        $factory = (new Factory())->withServiceAccount(self::$serviceAccount);

        $this->assertFunctioningConnection($factory);
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function withGoogleApplicationCredentialsAsFilePath(): void
    {
        Util::putenv('GOOGLE_APPLICATION_CREDENTIALS', self::$credentialsPath);

        $this->assertFunctioningConnection(new Factory());
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function withGoogleApplicationCredentialsAsJsonString(): void
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
        } finally {
            if ($user !== null) {
                $auth->deleteUser($user->uid);
            }
        }
    }
}

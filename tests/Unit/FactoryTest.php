<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Tests\UnitTestCase;
use RuntimeException;

/**
 * @internal
 */
final class FactoryTest extends UnitTestCase
{
    private string $serviceAccount;

    protected function setUp(): void
    {
        $this->serviceAccount = self::$fixturesDir.'/ServiceAccount/valid.json';
    }

    public function testItUsesTheCredentialsFromTheGooglaApplicationCredentialsEnvironmentVariable(): void
    {
        \putenv('GOOGLE_APPLICATION_CREDENTIALS='.$this->serviceAccount);

        $this->assertServices(new Factory());

        \putenv('GOOGLE_APPLICATION_CREDENTIALS');
    }

    public function testItNeedsCredentials(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/credential/');

        (new Factory())->withDisabledAutoDiscovery()->createApiClient();
    }

    public function testItUsesAServiceAccount(): void
    {
        $factory = (new Factory())->withServiceAccount($this->serviceAccount);

        $this->assertServices($factory);
    }

    public function testAProjectIdCanBeProvidedAsAGoogleCloudProjectEnvironmentVariable(): void
    {
        // The database component requires a project ID
        \putenv('GOOGLE_CLOUD_PROJECT=project-id');

        (new Factory())->createDatabase();

        $this->addToAssertionCount(1);

        \putenv('GOOGLE_CLOUD_PROJECT');
    }

    public function testItFailsWithoutAProjectId(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Unable to/');

        (new Factory())->withDisabledAutoDiscovery()->createApiClient();
    }

    private function assertServices(Factory $factory): void
    {
        $factory->createAuth();
        $this->addToAssertionCount(1);

        $factory->createDatabase();
        $this->addToAssertionCount(1);

        $factory->createDynamicLinksService();
        $this->addToAssertionCount(1);

        $factory->createFirestore();
        $this->addToAssertionCount(1);

        $factory->createMessaging();
        $this->addToAssertionCount(1);

        $factory->createRemoteConfig();
        $this->addToAssertionCount(1);

        $factory->createStorage();
        $this->addToAssertionCount(1);
    }
}

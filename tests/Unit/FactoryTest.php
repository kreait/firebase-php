<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit;

use Beste\Json;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Tests\UnitTestCase;

use function putenv;

/**
 * @internal
 *
 * @phpstan-import-type ServiceAccountShape from Factory
 */
final class FactoryTest extends UnitTestCase
{
    /** @var non-empty-string */
    private string $serviceAccountFilePath;

    /**
     * @var ServiceAccountShape
     */
    private array $serviceAccountArray;

    protected function setUp(): void
    {
        $this->serviceAccountFilePath = self::$fixturesDir.'/ServiceAccount/valid.json';
        $this->serviceAccountArray = Json::decodeFile($this->serviceAccountFilePath, true);
    }

    public function testItUsesTheCredentialsFromTheGoogleApplicationCredentialsEnvironmentVariable(): void
    {
        putenv('GOOGLE_APPLICATION_CREDENTIALS='.$this->serviceAccountFilePath);

        $this->assertServices(new Factory());

        putenv('GOOGLE_APPLICATION_CREDENTIALS');
    }

    public function testItCanBeConfiguredWithThePathToAServiceAccount(): void
    {
        $factory = (new Factory())->withServiceAccount($this->serviceAccountFilePath);

        $this->assertServices($factory);
    }

    public function testItCanBeConfiguredWithAServiceAccountArray(): void
    {
        $factory = (new Factory())->withServiceAccount($this->serviceAccountArray);

        $this->assertServices($factory);
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

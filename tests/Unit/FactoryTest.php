<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit;

use Beste\Clock\FrozenClock;
use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Http\HttpClientOptions;
use Kreait\Firebase\Tests\UnitTestCase;
use Psr\Cache\CacheItemPoolInterface;
use RuntimeException;
use StellaMaris\Clock\ClockInterface;

/**
 * @internal
 */
final class FactoryTest extends UnitTestCase
{
    private string $serviceAccount;
    private string $projectId;
    private string $clientEmail;

    protected function setUp(): void
    {
        $this->serviceAccount = self::$fixturesDir.'/ServiceAccount/valid.json';

        $json = \json_decode(\file_get_contents($this->serviceAccount), true);
        \assert(\is_array($json));

        $this->projectId = $json['project_id'];
        $this->clientEmail = $json['client_email'];
    }

    public function testItUsesACustomDatabaseUri(): void
    {
        $uri = new Uri($expected = 'http://domain.tld/');

        $factory = (new Factory())->withDatabaseUri($uri);

        $this->assertSame($expected, \invade($factory)->getDatabaseUri()->__toString());
    }

    public function testItUsesACustomDefaultStorageBucket(): void
    {
        $factory = (new Factory())->withDefaultStorageBucket('foo');

        $this->assertSame('foo', \invade($factory)->getStorageBucketName());
    }

    public function testItUsesTheCredentialsFromTheGooglaApplicationCredentialsEnvironmentVariable(): void
    {
        \putenv('GOOGLE_APPLICATION_CREDENTIALS='.$this->serviceAccount);

        $factory = (new Factory());

        $this->assertSame($this->projectId, \invade($factory)->getProjectId());
        $this->assertSame($this->clientEmail, \invade($factory)->getClientEmail());

        $this->assertServices($factory);

        \putenv('GOOGLE_APPLICATION_CREDENTIALS');
    }

    public function testItUsesACustomClientEmail(): void
    {
        $factory = (new Factory())
            ->withDisabledAutoDiscovery()
            ->withClientEmail($email = 'does@not.matter')
        ;

        $this->assertSame($email, \invade($factory)->getClientEmail());
        $this->expectException(RuntimeException::class);
        $this->assertNull(\invade($factory)->getProjectId());
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

        $this->assertSame($this->clientEmail, \invade($factory)->getClientEmail());
        $this->assertSame($this->projectId, \invade($factory)->getProjectId());

        $this->assertServices($factory);
    }

    public function testItUsesAClock(): void
    {
        $factory = (new Factory())->withClock($clock = FrozenClock::fromUTC());

        $this->assertInstanceOf(ClockInterface::class, \invade($factory)->clock);
    }

    public function testItWrapsANonClockInAClock(): void
    {
        $clock = new class() {
            public function now(): \DateTimeImmutable
            {
                return new \DateTimeImmutable();
            }
        };

        $factory = (new Factory())->withClock($clock);

        $this->assertInstanceOf(ClockInterface::class, \invade($factory)->clock);
    }

    public function testItUsesAVerifierCache(): void
    {
        $factory = (new Factory())->withVerifierCache($cache = $this->createMock(CacheItemPoolInterface::class));

        $this->assertSame($cache, \invade($factory)->verifierCache);
    }

    public function testItUsesAnAuthTokenCache(): void
    {
        $factory = (new Factory())->withAuthTokenCache($cache = $this->createMock(CacheItemPoolInterface::class));

        $this->assertSame($cache, \invade($factory)->authTokenCache);
    }

    public function testItUsesAProjectId(): void
    {
        $factory = (new Factory())->withDisabledAutoDiscovery()->withProjectId($projectId = 'a-custom-project-id');

        $this->assertProjectId($factory, $projectId);
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

    public function testItAcceptsNewHttpClientOptions(): void
    {
        $factory = (new Factory())->withHttpClientOptions($options = HttpClientOptions::default());

        $this->assertSame($options, \invade($factory)->httpClientOptions);
    }

    private function assertProjectId(Factory $factory, ?string $expected): void
    {
        $this->assertSame($expected, \invade($factory)->getProjectId());
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

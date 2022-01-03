<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit;

use Beste\Clock\FrozenClock;
use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Http\HttpClientOptions;
use Kreait\Firebase\Tests\UnitTestCase;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Clock\ClockInterface;
use RuntimeException;

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

        $this->assertDatabaseUri($factory, $expected);
    }

    public function testItUsesACustomDefaultStorageBucket(): void
    {
        $factory = (new Factory())->withDefaultStorageBucket($bucket = 'foo');

        $this->assertStorageBucket($factory, $bucket);
    }

    public function testItUsesTheCredentialsFromTheGooglaApplicationCredentialsEnvironmentVariable(): void
    {
        \putenv('GOOGLE_APPLICATION_CREDENTIALS='.$this->serviceAccount);

        $factory = (new Factory());

        $this->assertProjectId($factory, $this->projectId);
        $this->assertClientEmail($factory, $this->clientEmail);

        $this->assertServices($factory);

        \putenv('GOOGLE_APPLICATION_CREDENTIALS');
    }

    public function testItUsesACustomClientEmail(): void
    {
        $factory = (new Factory())
            ->withDisabledAutoDiscovery()
            ->withClientEmail($email = 'does@not.matter')
        ;

        $this->assertClientEmail($factory, $email);
        $this->expectException(RuntimeException::class);
        $this->assertProjectId($factory, null);
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

        $this->assertProjectId($factory, $this->projectId);
        $this->assertClientEmail($factory, $this->clientEmail);

        $this->assertServices($factory);
    }

    public function testItUsesAClock(): void
    {
        $factory = (new Factory())->withClock($clock = FrozenClock::fromUTC());

        $this->assertClock($factory, $clock);
    }

    public function testItUsesAVerifierCache(): void
    {
        $factory = (new Factory())->withVerifierCache($cache = $this->createMock(CacheItemPoolInterface::class));

        $this->assertVerifierCache($factory, $cache);
    }

    public function testItUsesAnAuthTokenCache(): void
    {
        $factory = (new Factory())->withAuthTokenCache($cache = $this->createMock(CacheItemPoolInterface::class));

        $this->assertAuthTokenCache($factory, $cache);
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

        $this->assertHttpClientOptions($factory, $options);
    }

    private function assertProjectId(Factory $factory, ?string $expected): void
    {
        $method = (new \ReflectionObject($factory))->getMethod('getProjectId');
        $method->setAccessible(true);

        $value = $method->invoke($factory);

        $this->assertSame($expected, $value);
    }

    private function assertClientEmail(Factory $factory, string $expected): void
    {
        $method = (new \ReflectionObject($factory))->getMethod('getClientEmail');
        $method->setAccessible(true);

        $value = $method->invoke($factory);

        $this->assertSame($expected, $value);
    }

    private function assertDatabaseUri(Factory $factory, string $expected): void
    {
        $method = (new \ReflectionObject($factory))->getMethod('getDatabaseUri');
        $method->setAccessible(true);

        $value = $method->invoke($factory);

        $this->assertSame($expected, (string) $value);
    }

    private function assertStorageBucket(Factory $factory, string $expected): void
    {
        $method = (new \ReflectionObject($factory))->getMethod('getStorageBucketName');
        $method->setAccessible(true);

        $value = $method->invoke($factory);

        $this->assertSame($expected, (string) $value);
    }

    private function assertClock(Factory $factory, ClockInterface $expected): void
    {
        $property = (new \ReflectionObject($factory))->getProperty('clock');
        $property->setAccessible(true);

        $this->assertSame($expected, $property->getValue($factory));
    }

    private function assertVerifierCache(Factory $factory, CacheItemPoolInterface $expected): void
    {
        $property = (new \ReflectionObject($factory))->getProperty('verifierCache');
        $property->setAccessible(true);

        $this->assertSame($expected, $property->getValue($factory));
    }

    private function assertAuthTokenCache(Factory $factory, CacheItemPoolInterface $expected): void
    {
        $property = (new \ReflectionObject($factory))->getProperty('authTokenCache');
        $property->setAccessible(true);

        $this->assertSame($expected, $property->getValue($factory));
    }

    private function assertHttpClientOptions(Factory $factory, HttpClientOptions $expected): void
    {
        $property = (new \ReflectionObject($factory))->getProperty('httpClientOptions');
        $property->setAccessible(true);

        $this->assertSame($expected, $property->getValue($factory));
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

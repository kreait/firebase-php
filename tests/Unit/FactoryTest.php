<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit;

use DateTimeImmutable;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\Credentials\UserRefreshCredentials;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Uri;
use InvalidArgumentException;
use Kreait\Clock\FrozenClock;
use Kreait\Firebase\Auth\CustomTokenViaGoogleIam;
use Kreait\Firebase\Auth\DisabledLegacyCustomTokenGenerator;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Tests\UnitTestCase;
use Psr\SimpleCache\CacheInterface;
use RuntimeException;

/**
 * @internal
 */
class FactoryTest extends UnitTestCase
{
    /** @var string */
    private $validServiceAccountFile;

    /** @var ServiceAccount */
    private $validServiceAccount;

    /** @var UserRefreshCredentials */
    private $userRefreshCredentials;

    /** @var Factory */
    private $factory;

    protected function setUp(): void
    {
        $this->validServiceAccountFile = self::$fixturesDir.'/ServiceAccount/valid.json';
        $this->validServiceAccount = ServiceAccount::fromValue($this->validServiceAccountFile);

        \putenv('SUPPRESS_GCLOUD_CREDS_WARNING=true');
        $this->userRefreshCredentials = new UserRefreshCredentials(Factory::API_CLIENT_SCOPES, self::$fixturesDir.'/user_refresh_credentials.json');

        $this->factory = (new Factory());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        \putenv('SUPPRESS_GCLOUD_CREDS_WARNING');
    }

    public function testItAcceptsACustomDatabaseUri(): void
    {
        $uri = new Uri('http://domain.tld/');

        $database = (new Factory())
            ->withServiceAccount($this->validServiceAccount)
            ->withDatabaseUri($uri)
            ->createDatabase();

        $databaseUri = $database->getReference()->getUri();

        $this->assertSame($uri->getScheme(), $databaseUri->getScheme());
        $this->assertSame($uri->getHost(), $databaseUri->getHost());
    }

    public function testItAcceptsACustomDefaultStorageBucket(): void
    {
        $storage = (new Factory())
            ->withServiceAccount($this->validServiceAccount)
            ->withDefaultStorageBucket('foo')
            ->createStorage();

        $this->assertSame('foo', $storage->getBucket()->name());
    }

    public function testCreateCustomTokenGeneratorWithApplicationDefaultCredentials(): void
    {
        \putenv('GOOGLE_APPLICATION_CREDENTIALS='.$this->validServiceAccountFile);

        $generator = (new Factory())->createCustomTokenGenerator();
        $this->assertNotInstanceOf(DisabledLegacyCustomTokenGenerator::class, $generator);

        \putenv('GOOGLE_APPLICATION_CREDENTIALS');
    }

    public function testCreateCustomTokenGeneratorWithClientEmailOnly(): void
    {
        $generator = (new Factory())
            ->withDisabledAutoDiscovery()
            ->withClientEmail('does@not.matter')
            ->createCustomTokenGenerator();

        $this->assertInstanceOf(CustomTokenViaGoogleIam::class, $generator);
    }

    public function testCreateStorageWithApplicationDefaultCredentials(): void
    {
        \putenv('GOOGLE_APPLICATION_CREDENTIALS='.$this->validServiceAccountFile);

        // Only with a valid service account is the default bucket available
        (new Factory())->createStorage()->getBucket();
        $this->addToAssertionCount(1);

        \putenv('GOOGLE_APPLICATION_CREDENTIALS');
    }

    public function testCreateStorageWithFirebaseCredentials(): void
    {
        \putenv('FIREBASE_CREDENTIALS='.$this->validServiceAccountFile);

        // Only with a valid service account is the default bucket available
        (new Factory())->createStorage()->getBucket();
        $this->addToAssertionCount(1);

        \putenv('FIREBASE_CREDENTIALS');
    }

    public function testItCannotCreateAStorageWithoutCredentials(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Unable to/');

        (new Factory())->withDisabledAutoDiscovery()->createStorage();
    }

    public function testItAcceptsAServiceAccount(): void
    {
        (new Factory())->withServiceAccount($this->validServiceAccount);
        $this->addToAssertionCount(1);
    }

    public function testItAcceptsAClock(): void
    {
        (new Factory())->withClock(new FrozenClock(new DateTimeImmutable()));
        $this->addToAssertionCount(1);
    }

    public function testItAcceptsAVerifierCache(): void
    {
        (new Factory())->withVerifierCache($this->createMock(CacheInterface::class));
        $this->addToAssertionCount(1);
    }

    public function testDynamicLinksCanBeCreatedWithoutADefaultDomain(): void
    {
        $this->factory->createDynamicLinksService();
        $this->addToAssertionCount(1);
    }

    public function testCreateApiClientWithCustomHandlerStack(): void
    {
        $stack = HandlerStack::create();

        $apiClient = $this->factory->createApiClient(['handler' => $stack]);

        $this->assertSame($stack, $apiClient->getConfig('handler'));
    }

    public function testCustomTokenGenerationIsDisabledWithMissingRequirements(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/disabled/');
        (new Factory())->withDisabledAutoDiscovery()->createAuth()->createCustomToken('uid');
    }

    public function testIdTokenVerificationIsDisabledWithMissingRequirements(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/disabled/');

        (new Factory())->withDisabledAutoDiscovery()->createAuth()->verifyIdToken('idtoken');
    }

    public function testIdTokenVerificationIsPossibleWithoutCredentialsButAProjectId(): void
    {
        // Invalid argument means that the ID Token Verifier tried to verify but couldn't,
        // meaning it works :)
        $this->expectException(InvalidArgumentException::class);

        (new Factory())
            ->withDisabledAutoDiscovery()
            ->withProjectId('project-id')
            ->createAuth()
            ->verifyIdToken('idtoken');
    }

    public function testAProjectIdCanBeProvidedDirectly(): void
    {
        // The database component requires a project ID
        (new Factory())->withDisabledAutoDiscovery()->withProjectId('project-id')->createDatabase();
        $this->addToAssertionCount(1);
    }

    public function testAProjectIdCanBeProvidedViaAServiceAccount(): void
    {
        // The database component requires a project ID
        (new Factory())->withServiceAccount($this->validServiceAccount)->createDatabase();
        $this->addToAssertionCount(1);
    }

    public function testAProjectIdCanBeProvidedViaCredentials(): void
    {
        // The database component requires a project ID
        (new Factory())
            ->withGoogleAuthTokenCredentials(new ServiceAccountCredentials(Factory::API_CLIENT_SCOPES, $this->validServiceAccountFile))
            ->createDatabase();

        $this->addToAssertionCount(1);
    }

    public function testAProjectIdCanBeProvidedAsAGoogleCloudProjectEnvironmentVariable(): void
    {
        // The database component requires a project ID
        \putenv('GOOGLE_CLOUD_PROJECT=project-id');

        (new Factory())
            ->withGoogleAuthTokenCredentials($this->userRefreshCredentials)
            ->createDatabase();

        $this->addToAssertionCount(1);

        \putenv('GOOGLE_CLOUD_PROJECT');
    }

    public function testAProjectIdCanBeProvidedAsAGCloudProjectEnvironmentVariable(): void
    {
        // The database component requires a project ID
        \putenv('GCLOUD_PROJECT=project-id');

        (new Factory())
            ->withGoogleAuthTokenCredentials($this->userRefreshCredentials)
            ->createDatabase();

        $this->addToAssertionCount(1);

        \putenv('GCLOUD_PROJECT');
    }

    public function testItFailsWhenNoProjectIdCouldBeDetermined(): void
    {
        // User Refresh Credentials don't provide a project ID
        // The database component requires a project ID
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Unable to/');

        (new Factory())
            ->withGoogleAuthTokenCredentials($this->userRefreshCredentials)
            ->createDatabase();
    }

    public function testWithoutAProjectIdTheStorageComponentNeedsABucketName(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/no default/');

        (new Factory())
            ->withGoogleAuthTokenCredentials($this->userRefreshCredentials)
            ->createStorage()
            ->getBucket();
    }

    public function testNoRemoteConfigWithoutAProjectId(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Unable to/');

        (new Factory())->withDisabledAutoDiscovery()->createRemoteConfig();
    }

    public function testNoMessagingWithoutAProjectId(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Unable to/');

        (new Factory())->withDisabledAutoDiscovery()->createMessaging();
    }

    public function testNoFirestoreWithoutCredentials(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Unable to/');

        (new Factory())->withDisabledAutoDiscovery()->createFirestore();
    }

    public function testItAcceptsACustomGuzzleHttpHandler(): void
    {
        $this->factory->createApiClient(['handler' => new MockHandler()]);
        $this->addToAssertionCount(1);
    }
}

<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit;

use DateTimeImmutable;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\Credentials\UserRefreshCredentials;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Uri;
use InvalidArgumentException;
use Kreait\Clock\FrozenClock;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\ServiceAccount\Discoverer;
use Kreait\Firebase\Tests\UnitTestCase;
use Psr\SimpleCache\CacheInterface;
use RuntimeException;

/**
 * @internal
 */
class FactoryTest extends UnitTestCase
{
    /**
     * @var string
     */
    private $validServiceAccountFile;

    /**
     * @var ServiceAccount
     */
    private $validServiceAccount;

    /**
     * @var UserRefreshCredentials
     */
    private $userRefreshCredentials;

    /**
     * @var Factory
     */
    private $factory;

    protected function setUp()
    {
        $this->validServiceAccountFile = self::$fixturesDir.'/ServiceAccount/valid.json';
        $this->validServiceAccount = ServiceAccount::fromValue($this->validServiceAccountFile);

        \putenv('SUPPRESS_GCLOUD_CREDS_WARNING=true');
        $this->userRefreshCredentials = new UserRefreshCredentials(Factory::API_CLIENT_SCOPES, self::$fixturesDir.'/user_refresh_credentials.json');

        $discoverer = $this->createMock(Discoverer::class);
        $discoverer
            ->method('discover')
            ->willReturn($this->validServiceAccount);

        $this->factory = (new Factory())->withServiceAccountDiscoverer($discoverer);
    }

    protected function tearDown()
    {
        parent::tearDown();

        \putenv('SUPPRESS_GCLOUD_CREDS_WARNING');
    }

    public function testItAcceptsACustomDatabaseUri()
    {
        $uri = new Uri('http://domain.tld/');

        $database = $this->factory
            ->withServiceAccount($this->validServiceAccount)
            ->withDatabaseUri($uri)
            ->createDatabase();

        $databaseUri = $database->getReference()->getUri();

        $this->assertSame($uri->getScheme(), $databaseUri->getScheme());
        $this->assertSame($uri->getHost(), $databaseUri->getHost());
    }

    public function testItAcceptsACustomDefaultStorageBucket()
    {
        $storage = $this->factory
            ->withServiceAccount($this->validServiceAccount)
            ->withDefaultStorageBucket('foo')
            ->createStorage();

        $this->assertSame('foo', $storage->getBucket()->name());
    }

    public function testCreateStorageWithApplicationDefaultCredentials()
    {
        \putenv('GOOGLE_APPLICATION_CREDENTIALS='.$this->validServiceAccountFile);

        (new Factory())->createStorage();
        $this->addToAssertionCount(1);

        \putenv('GOOGLE_APPLICATION_CREDENTIALS');
    }

    public function testCreateStorageWithFirebaseCredentials()
    {
        \putenv('FIREBASE_CREDENTIALS='.$this->validServiceAccountFile);

        (new Factory())->createStorage();
        $this->addToAssertionCount(1);

        \putenv('FIREBASE_CREDENTIALS');
    }

    public function testItCannotCreateAStorageWithoutCredentials()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageRegExp('/Unable to/');

        (new Factory())->withDisabledAutoDiscovery()->createStorage();
    }

    public function testItAcceptsAServiceAccount()
    {
        $this->factory->withServiceAccount($this->validServiceAccount);
        $this->addToAssertionCount(1);
    }

    public function testItAcceptsAClock()
    {
        $this->factory->withClock(new FrozenClock(new DateTimeImmutable()));
        $this->addToAssertionCount(1);
    }

    public function testItAcceptsAVerifierCache()
    {
        $this->factory->withVerifierCache($this->createMock(CacheInterface::class));
        $this->addToAssertionCount(1);
    }

    public function testItAcceptsACustomHttpClientConfig()
    {
        $apiClient = $this->factory->withHttpClientConfig(['key' => 'value'])->createApiClient();

        $this->assertSame('value', $apiClient->getConfig('key'));
    }

    public function testItAcceptsAdditionalHttpClientMiddlewares()
    {
        $this->factory->withHttpClientMiddlewares([
            static function () {},
            'name' => static function () {},
        ])->createApiClient();

        $this->addToAssertionCount(1);
    }

    public function testDynamicLinksCanBeCreatedWithoutADefaultDomain()
    {
        $this->factory->createDynamicLinksService();
        $this->addToAssertionCount(1);
    }

    public function testCreateApiClientWithCustomHandlerStack()
    {
        $stack = HandlerStack::create();

        $apiClient = $this->factory->createApiClient(['handler' => $stack]);

        $this->assertSame($stack, $apiClient->getConfig('handler'));
    }

    public function testCustomTokenGenerationIsDisabledWithMissingRequirements()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageRegExp('/disabled/');
        (new Factory())->withDisabledAutoDiscovery()->createAuth()->createCustomToken('uid');
    }

    public function testIdTokenVerificationIsDisabledWithMissingRequirements()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageRegExp('/disabled/');

        (new Factory())->withDisabledAutoDiscovery()->createAuth()->verifyIdToken('idtoken');
    }

    public function testIdTokenVerificationIsPossibleWithoutCredentialsButAProjectId()
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

    public function testAProjectIdCanBeProvidedDirectly()
    {
        // The database component requires a project ID
        (new Factory())->withDisabledAutoDiscovery()->withProjectId('project-id')->createDatabase();
        $this->addToAssertionCount(1);
    }

    public function testAProjectIdCanBeProvidedViaAServiceAccount()
    {
        // The database component requires a project ID
        (new Factory())->withServiceAccount($this->validServiceAccount)->createDatabase();
        $this->addToAssertionCount(1);
    }

    public function testAProjectIdCanBeProvidedViaCredentials()
    {
        // The database component requires a project ID
        (new Factory())
            ->withGoogleAuthTokenCredentials(new ServiceAccountCredentials(Factory::API_CLIENT_SCOPES, $this->validServiceAccountFile))
            ->createDatabase();

        $this->addToAssertionCount(1);
    }

    public function testAProjectIdCanBeProvidedAsAGoogleCloudProjectEnvironmentVariable()
    {
        // The database component requires a project ID
        \putenv('GOOGLE_CLOUD_PROJECT=project-id');

        (new Factory())
            ->withGoogleAuthTokenCredentials($this->userRefreshCredentials)
            ->createDatabase();

        $this->addToAssertionCount(1);

        \putenv('GOOGLE_CLOUD_PROJECT');
    }

    public function testAProjectIdCanBeProvidedAsAGCloudProjectEnvironmentVariable()
    {
        // The database component requires a project ID
        \putenv('GCLOUD_PROJECT=project-id');

        (new Factory())
            ->withGoogleAuthTokenCredentials($this->userRefreshCredentials)
            ->createDatabase();

        $this->addToAssertionCount(1);

        \putenv('GCLOUD_PROJECT');
    }

    public function testItFailsWhenNoProjectIdCouldBeDetermined()
    {
        // User Refresh Credentials don't provide a project ID
        // The database component requires a project ID
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageRegExp('/Unable to/');

        (new Factory())
            ->withGoogleAuthTokenCredentials($this->userRefreshCredentials)
            ->createDatabase();
    }

    public function testWithoutAProjectIdTheStorageComponentNeedsABucketName()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageRegExp('/no default/');

        (new Factory())
            ->withGoogleAuthTokenCredentials($this->userRefreshCredentials)
            ->createStorage()
            ->getBucket();
    }

    public function testNoRemoteConfigWithoutAProjectId()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageRegExp('/Unable to/');

        (new Factory())->withDisabledAutoDiscovery()->createRemoteConfig();
    }

    public function testNoMessagingWithoutAProjectId()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageRegExp('/Unable to/');

        (new Factory())->withDisabledAutoDiscovery()->createMessaging();
    }

    public function testNoFirestoreWithoutCredentials()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageRegExp('/Unable to/');

        (new Factory())->withDisabledAutoDiscovery()->createFirestore();
    }
}

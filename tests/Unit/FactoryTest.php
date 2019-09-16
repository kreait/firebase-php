<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit;

use DateTimeImmutable;
use GuzzleHttp\Psr7\Uri;
use Kreait\Clock\FrozenClock;
use Kreait\Firebase\Exception\LogicException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\ServiceAccount\Discoverer;
use Kreait\Firebase\Tests\UnitTestCase;
use Psr\SimpleCache\CacheInterface;

/**
 * @internal
 */
class FactoryTest extends UnitTestCase
{
    /**
     * @var ServiceAccount
     */
    private $serviceAccount;

    /**
     * @var Factory
     */
    private $factory;

    protected function setUp()
    {
        $this->serviceAccount = ServiceAccount::fromJsonFile(self::$fixturesDir.'/ServiceAccount/valid.json');

        $discoverer = $this->createMock(Discoverer::class);
        $discoverer
            ->method('discover')
            ->willReturn($this->serviceAccount);

        $this->factory = (new Factory())->withServiceAccountDiscoverer($discoverer);
    }

    public function testItAcceptsACustomDatabaseUri()
    {
        $uri = new Uri('http://domain.tld/');
        $databaseUri = $this->factory->withDatabaseUri($uri)->createDatabase()->getReference()->getUri();

        $this->assertSame($uri->getScheme(), $databaseUri->getScheme());
        $this->assertSame($uri->getHost(), $databaseUri->getHost());
    }

    public function testItAcceptsACustomDefaultStorageBucket()
    {
        $storage = $this->factory->withDefaultStorageBucket('foo')->createStorage();

        $this->assertSame('foo', $storage->getBucket()->name());
    }

    public function testItAcceptsAServiceAccount()
    {
        $this->factory->withServiceAccount($this->serviceAccount);
        $this->addToAssertionCount(1);
    }

    public function testItAcceptsAClock()
    {
        $this->factory->withClock(new FrozenClock(new DateTimeImmutable()));
        $this->addToAssertionCount(1);
    }

    public function testItAcceptsAnAuthOverride()
    {
        $this->factory->asUser('some-uid', ['some' => 'claim']);
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

    public function testServiceAccountDiscoveryCanBeDisabled()
    {
        $this->expectException(LogicException::class);
        $this->factory->withDisabledAutoDiscovery()->createAuth();
    }

    public function testDynamicLinksCanBeCreatedWithoutADefaultDomain()
    {
        $this->factory->createDynamicLinksService();
        $this->addToAssertionCount(1);
    }
}

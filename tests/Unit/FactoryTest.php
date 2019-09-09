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
        $firebase = $this->factory->withDefaultStorageBucket('foo')->create();

        $this->assertSame('foo', $firebase->getStorage()->getBucket()->name());
    }

    public function testItAcceptsAServiceAccount()
    {
        $this->factory->withServiceAccount($this->serviceAccount)->create();
        $this->addToAssertionCount(1);
    }

    public function testItAcceptsAClock()
    {
        $this->factory->withClock(new FrozenClock(new DateTimeImmutable()))->create();
        $this->addToAssertionCount(1);
    }

    public function testItAcceptsAnAuthOverride()
    {
        $this->factory->asUser('some-uid', ['some' => 'claim'])->create();
        $this->addToAssertionCount(1);
    }

    public function testItAcceptsAVerifierCache()
    {
        $cache = $this->createMock(CacheInterface::class);

        $this->factory->withVerifierCache($cache)->create();
        $this->addToAssertionCount(1);
    }

    public function testItAcceptsACustomHttpClientConfig()
    {
        $this->factory->withHttpClientConfig(['key' => 'value'])->create();
        $this->addToAssertionCount(1);
    }

    public function testItAcceptsAdditionalHttpClientMiddlewares()
    {
        $this->factory->withHttpClientMiddlewares([
            static function () {},
            'name' => static function () {},
        ])->create();

        $this->addToAssertionCount(1);
    }

    public function testServiceAccountDiscoveryCanBeDisabled()
    {
        $this->expectException(LogicException::class);
        $this->factory->withDisabledAutoDiscovery()->create();
    }

    public function testDynamicLinksCanBeCreatedWithoutADefaultDomain()
    {
        $this->factory->createDynamicLinksService();
        $this->addToAssertionCount(1);
    }
}

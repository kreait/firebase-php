<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase;
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
     * @var Discoverer
     */
    private $discoverer;

    /**
     * @var Factory
     */
    private $factory;

    protected function setUp()
    {
        $this->serviceAccount = ServiceAccount::fromJsonFile(self::$fixturesDir.'/ServiceAccount/valid.json');

        $this->discoverer = $this->createMock(Discoverer::class);
        $this->discoverer
            ->method('discover')
            ->willReturn($this->serviceAccount);

        $this->factory = (new Factory())->withServiceAccountDiscoverer($this->discoverer);
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
        $factory = $this->factory->withDefaultStorageBucket('foo');

        $firebase = $factory->create();

        $this->assertInstanceOf(Firebase::class, $firebase);
        $this->assertSame('foo', $firebase->getStorage()->getBucket()->name());
    }

    public function testItAcceptsAServiceAccount()
    {
        $factory = $this->factory->withServiceAccount($this->serviceAccount);

        $this->assertInstanceOf(Firebase::class, $factory->create());
    }

    public function testItAcceptsAnAuthOverride()
    {
        $factory = $this->factory->asUser('some-uid', ['some' => 'claim']);

        $this->assertInstanceOf(Firebase::class, $factory->create());
    }

    public function testItAcceptsAVerifierCache()
    {
        $cache = $this->createMock(CacheInterface::class);

        $factory = $this->factory->withVerifierCache($cache);

        $this->assertInstanceOf(Firebase::class, $factory->create());
    }

    public function testItAcceptsACustomHttpClientConfig()
    {
        $factory = $this->factory->withHttpClientConfig(['key' => 'value']);

        $this->assertInstanceOf(Firebase::class, $factory->create());
    }

    public function testItAcceptsAdditionalHttpClientMiddlewares()
    {
        $factory = $this->factory->withHttpClientMiddlewares([
            static function () {},
            'name' => static function () {},
        ]);

        $this->assertInstanceOf(Firebase::class, $factory->create());
    }

    public function testServiceAccountDiscoveryCanBeDisabled()
    {
        $factory = $this->factory->withDisabledAutoDiscovery();

        $this->expectException(LogicException::class);
        $factory->create();
    }
}

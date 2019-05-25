<?php

namespace Kreait\Firebase\Tests\Unit;

use Kreait\Firebase;
use Kreait\Firebase\Exception\LogicException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\ServiceAccount\Discoverer;
use Kreait\Firebase\Tests\UnitTestCase;

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
        $this->discoverer->expects($this->any())
            ->method('discover')
            ->willReturn($this->serviceAccount);

        $this->factory = (new Factory())->withServiceAccountDiscoverer($this->discoverer);
    }

    public function testItAcceptsACustomDatabaseUri()
    {
        $factory = $this->factory->withDatabaseUri('http://domain.tld');

        $this->assertInstanceOf(Firebase::class, $factory->create());
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
        $cache = $this->createMock('\Psr\SimpleCache\CacheInterface');

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
            function () {},
            'name' => function () {},
        ]);

        $this->assertInstanceOf(Firebase::class, $factory->create());
    }

    public function testItRejectsAnInvalidVerifierCache()
    {
        $cache = $this->createMock(\stdClass::class);

        $this->expectException(Firebase\Exception\InvalidArgumentException::class);
        $this->factory->withVerifierCache($cache);
    }

    public function testServiceAccountDiscoveryCanBeDisabled()
    {
        $factory = $this->factory->withDisabledAutoDiscovery();

        $this->expectException(LogicException::class);
        $factory->create();
    }
}

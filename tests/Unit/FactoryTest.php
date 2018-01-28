<?php

namespace Kreait\Firebase\Tests\Unit;

use Kreait\Firebase;
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
}

<?php

namespace Kreait\Tests\Firebase;

use Firebase\Auth\Token\Handler;
use Kreait\Firebase;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount\Discoverer;
use Kreait\Tests\FirebaseTestCase;

class FactoryTest extends FirebaseTestCase
{
    /**
     * @var Factory
     */
    private $factory;

    protected function setUp()
    {
        $discoverer = $this->createMock(Discoverer::class);
        $discoverer->expects($this->any())
            ->method('discover')
            ->willReturn($this->createServiceAccountMock());

        $this->factory = (new Factory())->withServiceAccountDiscoverer($discoverer);
    }

    public function testItAcceptsACustomDatabaseUri()
    {
        $firebase = $this->factory
            ->withDatabaseUri('http://domain.tld')
            ->create();

        $this->assertInstanceOf(Firebase::class, $firebase);
    }

    public function testItUsesADefaultTokenHandler()
    {
        $this->assertInstanceOf(Handler::class, $this->factory->create()->getTokenHandler());
    }

    public function testItAcceptsACustomTokenHandler()
    {
        $handler = new Handler('projectId', 'clientEmail', 'privateKey');

        $firebase = $this->factory
            ->withTokenHandler($handler)
            ->create();

        $this->assertSame($handler, $firebase->getTokenHandler());
    }

    public function testItAcceptsAServiceAccount()
    {
        $firebase = $this->factory
            ->withServiceAccount($this->createServiceAccountMock())
            ->create();

        $this->assertInstanceOf(Firebase::class, $firebase);
    }
}

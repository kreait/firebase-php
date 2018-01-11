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
        $uri = 'http://domain.tld';

        $factory = $this->factory->withDatabaseUri($uri);

        $this->assertSame($uri, (string) $factory->getDatabaseUri());
    }

    public function testItUsesADefaultTokenHandler()
    {
        $this->assertInstanceOf(Handler::class, $this->factory->getTokenHandler());
    }

    public function testItAcceptsACustomTokenHandler()
    {
        $handler = new Handler('projectId', 'clientEmail', 'privateKey');

        $factory = $this->factory->withTokenHandler($handler);

        $this->assertSame($handler, $factory->getTokenHandler());
    }

    public function testItAcceptsCredentials()
    {
        $firebase = $this->factory
            ->withCredentials($this->fixturesDir.'/ServiceAccount/valid.json');

        $this->assertTrue($noExceptionHasBeenThrown = true);
    }

    public function testItAcceptsAServiceAccount()
    {
        $factory = $this->factory->withServiceAccount($serviceAccount = $this->createServiceAccountMock());

        $this->assertSame($serviceAccount, $factory->getServiceAccount());
    }

    public function testItAcceptsAnApiKey()
    {
        $this->assertSame($this->factory, $this->factory->withApiKey('foo'));
    }

    public function testItAcceptsAServiceAccountAndApiKey()
    {
        $firebase = $this->factory
            ->withServiceAccountAndApiKey($this->createServiceAccountMock(), 'some key')
            ->create();

        $this->assertInstanceOf(Firebase::class, $firebase);
    }
}

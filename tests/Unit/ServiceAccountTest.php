<?php

namespace Kreait\Firebase\Tests\Unit;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Tests\UnitTestCase;

class ServiceAccountTest extends UnitTestCase
{
    private $validJsonFile;
    private $invalidJsonFile;
    private $malformedJsonFile;
    private $symlinkedJsonFile;
    private $unreadableJsonFile;

    /**
     * @var ServiceAccount
     */
    private $serviceAccount;

    protected function setUp()
    {
        $this->validJsonFile = self::$fixturesDir.'/ServiceAccount/valid.json';
        $this->malformedJsonFile = self::$fixturesDir.'/ServiceAccount/malformed.json';
        $this->invalidJsonFile = self::$fixturesDir.'/ServiceAccount/invalid.json';
        $this->symlinkedJsonFile = self::$fixturesDir.'/ServiceAccount/symlinked.json';
        $this->unreadableJsonFile = self::$fixturesDir.'/ServiceAccount/unreadable.json';

        @chmod($this->unreadableJsonFile, 0000);

        $this->serviceAccount = ServiceAccount::fromValue($this->validJsonFile);
    }

    protected function tearDown()
    {
        @chmod($this->unreadableJsonFile, 0644);
    }

    public function testGetters()
    {
        $data = json_decode(file_get_contents($this->validJsonFile), true);

        $this->assertSame($data['project_id'], $this->serviceAccount->getProjectId());
        $this->assertSame($data['client_id'], $this->serviceAccount->getClientId());
        $this->assertSame($data['client_email'], $this->serviceAccount->getClientEmail());
        $this->assertSame($data['private_key'], $this->serviceAccount->getPrivateKey());
    }

    public function testCreateFromJsonText()
    {
        $this->assertInstanceOf(
            ServiceAccount::class,
            ServiceAccount::fromValue(file_get_contents($this->validJsonFile))
        );
    }

    public function testCreateFromJsonFile()
    {
        $this->assertInstanceOf(ServiceAccount::class, ServiceAccount::fromValue($this->validJsonFile));
    }

    public function testCreateFromSymlinkedJsonFile()
    {
        $this->assertInstanceOf(ServiceAccount::class, ServiceAccount::fromValue($this->symlinkedJsonFile));
    }

    public function testCreateFromMissingFile()
    {
        $this->expectException(InvalidArgumentException::class);
        ServiceAccount::fromValue('missing.json');
    }

    public function testCreateFromMalformedJsonFile()
    {
        $this->expectException(InvalidArgumentException::class);
        ServiceAccount::fromValue($this->malformedJsonFile);
    }

    public function testCreateFromInvalidJsonFile()
    {
        $this->expectException(InvalidArgumentException::class);
        ServiceAccount::fromValue($this->invalidJsonFile);
    }

    public function testCreateFromDirectory()
    {
        $this->expectException(InvalidArgumentException::class);
        ServiceAccount::fromValue(__DIR__);
    }

    public function testCreateFromUnreadableFile()
    {
        $this->expectException(InvalidArgumentException::class);
        ServiceAccount::fromValue($this->unreadableJsonFile);
    }

    public function testCreateFromArray()
    {
        $data = json_decode(file_get_contents($this->validJsonFile), true);

        $this->assertInstanceOf(ServiceAccount::class, ServiceAccount::fromValue($data));
    }

    public function testCreateFromServiceAccount()
    {
        $serviceAccount = $this->createMock(ServiceAccount::class);

        $this->assertSame($serviceAccount, ServiceAccount::fromValue($serviceAccount));
    }

    public function testCreateFromInvalidValue()
    {
        $this->expectException(InvalidArgumentException::class);
        ServiceAccount::fromValue(false);
    }

    public function testCreateWithInvalidClientEmail()
    {
        $this->expectException(InvalidArgumentException::class);

        (new ServiceAccount())->withClientEmail('foo');
    }

    public function testWithCustomDiscoverer()
    {
        $discoverer = $this->createMock(ServiceAccount\Discoverer::class);
        $discoverer->expects($this->once())
            ->method('discover');

        ServiceAccount::discover($discoverer);
    }

    /**
     * @see https://github.com/kreait/firebase-php/issues/228
     *
     * @dataProvider sanitizableProjectIdProvider
     */
    public function testGetSanitizedProjectId($expected, $given)
    {
        $serviceAccount = ServiceAccount::fromJsonFile($this->validJsonFile);

        $previousSanitizedProjectId = $serviceAccount->getSanitizedProjectId();

        $serviceAccount = $serviceAccount->withProjectId($given);
        $sanitizedProjectId = $serviceAccount->getSanitizedProjectId();

        $this->assertSame($expected, $sanitizedProjectId);
        $this->assertNotSame($previousSanitizedProjectId, $sanitizedProjectId);
    }

    public function sanitizableProjectIdProvider()
    {
        return [
            ['example-com-api-project-xxxxxx', 'example.com:api-project-xxxxxx']
        ];
    }
}

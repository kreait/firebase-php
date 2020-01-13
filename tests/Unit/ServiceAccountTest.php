<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
class ServiceAccountTest extends UnitTestCase
{
    private $validJsonFile;
    private $realpathedValidJsonFile;
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
        $this->realpathedValidJsonFile = \realpath($this->validJsonFile);
        $this->malformedJsonFile = self::$fixturesDir.'/ServiceAccount/malformed.json';
        $this->invalidJsonFile = self::$fixturesDir.'/ServiceAccount/invalid.json';
        $this->symlinkedJsonFile = self::$fixturesDir.'/ServiceAccount/symlinked.json';
        $this->unreadableJsonFile = self::$fixturesDir.'/ServiceAccount/unreadable.json';

        @\chmod($this->unreadableJsonFile, 0000);
    }

    protected function tearDown()
    {
        @\chmod($this->unreadableJsonFile, 0644);
    }

    public function testGetters()
    {
        $serviceAccount = ServiceAccount::fromValue($this->validJsonFile);
        $data = \json_decode((string) \file_get_contents($this->validJsonFile), true);

        $this->assertSame($data['project_id'], $serviceAccount->getProjectId());
        $this->assertSame($data['client_id'], $serviceAccount->getClientId());
        $this->assertSame($data['client_email'], $serviceAccount->getClientEmail());
        $this->assertSame($data['private_key'], $serviceAccount->getPrivateKey());
        $this->assertSame($this->validJsonFile, $serviceAccount->getFilePath());
    }

    public function testCreateFromJsonText()
    {
        $serviceAccount = ServiceAccount::fromValue(\file_get_contents($this->validJsonFile));
        $this->assertNull($serviceAccount->getFilePath());
    }

    public function testCreateFromJsonFile()
    {
        $serviceAccount = ServiceAccount::fromValue($this->validJsonFile);
        $this->assertSame($this->validJsonFile, $serviceAccount->getFilePath());
    }

    public function testCreateFromRealpathedJsonFile()
    {
        $serviceAccount = ServiceAccount::fromValue($this->realpathedValidJsonFile);
        $this->assertSame($this->realpathedValidJsonFile, $serviceAccount->getFilePath());
    }

    public function testCreateFromSymlinkedJsonFile()
    {
        if ($this->onWindows()) {
            $this->markTestSkipped('Windows only support absolute symlinks');
        }

        $serviceAccount = ServiceAccount::fromValue($this->symlinkedJsonFile);
        $this->assertSame($this->symlinkedJsonFile, $serviceAccount->getFilePath());
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
        $data = \json_decode((string) \file_get_contents($this->validJsonFile), true);

        $serviceAccount = ServiceAccount::fromValue($data);
        $this->addToAssertionCount(1);
        $this->assertNull($serviceAccount->getFilePath());
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
        $expected = $this->createMock(ServiceAccount::class);

        $discoverer = $this->createMock(ServiceAccount\Discoverer::class);
        $discoverer
            ->method('discover')
            ->willReturn($expected);

        $this->assertSame($expected, ServiceAccount::discover($discoverer));
    }

    /**
     * @see https://github.com/kreait/firebase-php/issues/228
     *
     * @dataProvider sanitizableProjectIdProvider
     */
    public function testGetSanitizedProjectId($expected, $given)
    {
        $serviceAccount = ServiceAccount::fromJsonFile($this->validJsonFile)->withProjectId($given);

        $this->assertSame($given, $serviceAccount->getProjectId());
        $this->assertSame($expected, $serviceAccount->getSanitizedProjectId());
    }

    public function sanitizableProjectIdProvider()
    {
        return [
            ['example-com-api-project-xxxxxx', 'example.com:api-project-xxxxxx'],
        ];
    }

    private function onWindows()
    {
        return \mb_stripos(\PHP_OS, 'win') === 0;
    }
}

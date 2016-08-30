<?php

namespace Tests\Firebase;

use Firebase\Exception\InvalidArgumentException;
use Firebase\ServiceAccount;
use Tests\FirebaseTestCase;

class ServiceAccountTest extends FirebaseTestCase
{
    private $validJsonFile;
    private $invalidJsonFile;
    private $malformedJsonFile;

    /**
     * @var ServiceAccount
     */
    private $serviceAccount;

    protected function setUp()
    {
        $this->validJsonFile = $this->fixturesDir.'/ServiceAccount/valid.json';
        $this->malformedJsonFile = $this->fixturesDir.'/ServiceAccount/malformed.json';
        $this->invalidJsonFile = $this->fixturesDir.'/ServiceAccount/invalid.json';

        $this->serviceAccount = ServiceAccount::fromValue($this->validJsonFile);
    }

    public function testGetters()
    {
        $data = json_decode(file_get_contents($this->validJsonFile), true);

        $this->assertSame($data['project_id'], $this->serviceAccount->getProjectId());
        $this->assertSame($data['client_id'], $this->serviceAccount->getClientId());
        $this->assertSame($data['client_email'], $this->serviceAccount->getClientEmail());
        $this->assertSame($data['private_key'], $this->serviceAccount->getPrivateKey());
    }

    public function testCreateFromJsonFile()
    {
        $this->assertInstanceOf(ServiceAccount::class, ServiceAccount::fromValue($this->validJsonFile));
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
}

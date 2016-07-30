<?php

namespace Tests;

use Firebase\ServiceAccount;
use PHPUnit\Framework\TestCase;

abstract class FirebaseTestCase extends TestCase
{
    protected $fixturesDir = __DIR__.'/_fixtures';

    /**
     * @return ServiceAccount|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createServiceAccountMock()
    {
        $mock = $this->createMock(ServiceAccount::class);

        $mock->expects($this->any())
            ->method('getProjectId')
        ->willReturn('project');

        $mock->expects($this->any())
            ->method('getClientId')
        ->willReturn('client');

        $mock->expects($this->any())
            ->method('getClientEmail')
        ->willReturn('client@email.tld');

        $mock->expects($this->any())
            ->method('getPrivateKey')
        ->willReturn('some private key');

        return $mock;
    }
}

<?php

namespace Kreait\Tests\Firebase\ServiceAccount\Discovery;

use Kreait\Firebase\Exception\ServiceAccountDiscoveryFailed;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\ServiceAccount\Discovery\FromPath;
use Kreait\Tests\FirebaseTestCase;

class FromPathTest extends FirebaseTestCase
{
    public function testItWorks()
    {
        $method = new FromPath($this->fixturesDir.'/ServiceAccount/valid.json');
        $this->assertInstanceOf(ServiceAccount::class, $method());
    }

    public function testIfFails()
    {
        $this->expectException(ServiceAccountDiscoveryFailed::class);
        (new FromPath($this->fixturesDir.'/ServiceAccount/invalid.json'))();
    }
}

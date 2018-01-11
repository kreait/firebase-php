<?php

namespace Kreait\Tests\Firebase\Unit\ServiceAccount\Discovery;

use Kreait\Firebase\Exception\ServiceAccountDiscoveryFailed;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\ServiceAccount\Discovery\FromPath;
use Kreait\Tests\Firebase\Unit\UnitTestCase;

class FromPathTest extends UnitTestCase
{
    public function testItWorks()
    {
        $discoverer = new FromPath($this->fixturesDir.'/ServiceAccount/valid.json');
        $discoverer();

        $this->assertTrue($noExceptionWasThrown = true);
    }

    public function testItFails()
    {
        $this->expectException(ServiceAccountDiscoveryFailed::class);

        $discoverer = new FromPath($this->fixturesDir.'/ServiceAccount/invalid.json');
        $discoverer();
    }
}

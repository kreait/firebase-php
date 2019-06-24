<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\ServiceAccount\Discovery;

use Kreait\Firebase\Exception\ServiceAccountDiscoveryFailed;
use Kreait\Firebase\ServiceAccount\Discovery\FromPath;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
class FromPathTest extends UnitTestCase
{
    public function testItWorks()
    {
        $discoverer = new FromPath(self::$fixturesDir.'/ServiceAccount/valid.json');
        $discoverer();

        $this->assertTrue($noExceptionWasThrown = true);
    }

    public function testItFails()
    {
        $this->expectException(ServiceAccountDiscoveryFailed::class);

        $discoverer = new FromPath(self::$fixturesDir.'/ServiceAccount/invalid.json');
        $discoverer();
    }
}

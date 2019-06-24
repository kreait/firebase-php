<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\ServiceAccount\Discovery;

use Kreait\Firebase\Exception\ServiceAccountDiscoveryFailed;
use Kreait\Firebase\ServiceAccount\Discovery\FromGoogleWellKnownFile;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
class FromGoogleWellKnownFileTest extends UnitTestCase
{
    private $backup;

    protected function setUp()
    {
        $this->backup = \getenv('HOME');
    }

    protected function tearDown()
    {
        \putenv(\sprintf('%s=%s', 'HOME', $this->backup));
    }

    public function testItKnowsWhenTheFileIsInvalid()
    {
        $discoverer = new FromGoogleWellKnownFile();

        $this->expectException(ServiceAccountDiscoveryFailed::class);

        \putenv('HOME'); // This will let the Google CredentialsLoader return null
        $discoverer();
    }
}

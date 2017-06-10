<?php

namespace Kreait\Tests\Firebase\ServiceAccount\Discovery;

use Google\Auth\CredentialsLoader;
use Kreait\Firebase\Exception\ServiceAccountDiscoveryFailed;
use Kreait\Firebase\ServiceAccount\Discovery\FromGoogleWellKnownFile;
use Kreait\Tests\FirebaseTestCase;

class FromGoogleWellKnownFileTest extends FirebaseTestCase
{
    /**
     * @var FromGoogleWellKnownFile
     */
    private $method;

    private $backup;

    protected function setUp()
    {
        $this->backup = getenv('HOME');

        $this->method = new FromGoogleWellKnownFile();
    }

    protected function tearDown()
    {
        putenv(sprintf('%s=%s', 'HOME', $this->backup));
    }

    public function testItKnowsWhenTheFileIsInvalid()
    {
        putenv('HOME'); // This will let the Google CredentialsLoader return null

        $this->expectException(ServiceAccountDiscoveryFailed::class);
        ($this->method)();
    }
}

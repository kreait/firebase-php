<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\IdentityPlatform;

use Kreait\Firebase\Tests\UnitTestCase;
use Kreait\Firebase\Request\DefaultSupportedIdpConfig;

class DefaultSupportedIdpConfigTest extends UnitTestCase
{
    public function testWithProperties()
    {
        $properties = [
            'name' => 'testName',
            'enabled' => true,
            'clientId' => 'testClientId',
            'clientSecret' => 'testClientSecret'
        ];

        $instance = DefaultSupportedIdpConfig::withProperties($properties);

        $this->assertEquals($properties, $instance->toArray());
        $this->assertEquals($properties, $instance->jsonSerialize());
    }
}

<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\IdentityPlatform;

use Kreait\Firebase\Tests\UnitTestCase;
use Kreait\Firebase\IdentityPlatform\SpConfig;
use Kreait\Firebase\Value\Url;

class SpConfigTest extends UnitTestCase
{
    public function testWithProperties() : void
    {
        $properties = [
                'spEntityId' => 'testSp',
                'callbackUri' => 'https://google.com',
                'spCertificates' => []
        ];

        $propertiesConverted = $properties;
        $propertiesConverted['callbackUri'] = Url::fromValue($properties['callbackUri']);

        $instance = SpConfig::withProperties($properties);

        $this->assertEquals($propertiesConverted, $instance->toArray());
        $this->assertEquals($propertiesConverted, $instance->jsonSerialize());
    }
}

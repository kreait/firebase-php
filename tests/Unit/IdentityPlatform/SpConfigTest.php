<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\IdentityPlatform;

use Kreait\Firebase\Tests\UnitTestCase;
use Kreait\Firebase\IdentityPlatform\SpConfig;

class SpConfigTest extends UnitTestCase
{
    public function testWithProperties()
    {
        $properties = [


                'spEntityId' => 'testSp',
                'callbackUri' => 'https://google.com',
        ];

        $instance = SpConfig::withProperties($properties);

        $this->assertEquals($properties, $instance->toArray());
        $this->assertEquals($properties, $instance->jsonSerialize());
    }
}

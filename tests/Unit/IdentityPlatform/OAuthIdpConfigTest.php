<?php

namespace Kreait\Firebase\Tests\Unit\IdentityPlatform;

use Kreait\Firebase\IdentityPlatform\OAuthResponseType;
use Kreait\Firebase\Request\OAuthIdpConfig;
use Kreait\Firebase\Tests\UnitTestCase;
use Kreait\Firebase\Value\Url;

class OAuthIdpConfigTest extends UnitTestCase
{
    public function testWithProperties()
    {
        $properties = [
            'name' => 'oidc.test',
            'issuer' => 'https://google.com',
            'displayName' => 'Test OIDC',
            'enabled' => false,
            'clientId' => 'testclientid',
            'clientSecret' => 'testclientsecret',
            'responseType' => [ 'code' => true ]
        ];
        $propertiesConverted = $properties;
        $propertiesConverted['issuer'] = Url::fromValue($properties['issuer']);
        $propertiesConverted['responseType'] = OAuthResponseType::fromProperties($properties['responseType']);

        $instance = OAuthIdpConfig::withProperties($properties);

        $this->assertEquals($propertiesConverted, $instance->toArray());
        $this->assertEquals($propertiesConverted, $instance->jsonSerialize());
    }
}

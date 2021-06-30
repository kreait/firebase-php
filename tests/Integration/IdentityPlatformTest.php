<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Kreait\Firebase\Contract\IdentityPlatform;
use Kreait\Firebase\Exception\IdentityPlatform\ConfigurationNotFound;
use Kreait\Firebase\Exception\IdentityPlatformException;
use Kreait\Firebase\Tests\IntegrationTestCase;
use Kreait\Firebase\IdentityPlatform\DefaultSupportedIdpConfig;
use Kreait\Firebase\IdentityPlatform\InboundSamlConfig;
use Kreait\Firebase\IdentityPlatform\OAuthIdpConfig;
use Ramsey\Uuid\Nonstandard\Uuid;

/**
 * @internal
 */
class IdentityPlatformTest extends IntegrationTestCase
{
    protected string $testClientSecret;
    protected string $testClientId;
    protected IdentityPlatform $identityPlatform;


    protected function setUp(): void
    {
        $this->testClientSecret = Uuid::getFactory()->fromDateTime(\Carbon\Carbon::now())->toString();
        $this->testClientId = Uuid::getFactory()->fromDateTime(\Carbon\Carbon::now())->toString();
        $this->identityPlatform = $this->setupIdentityPlatform();
        $this->cleanup();
    }

    public function testListAllDefaultSupportedIdpConfigs() : void
    {
        $idps = $this->identityPlatform->listAllDefaultSupportedIdpConfigs();

        $this->assertContains('google.com', $idps, 'Should contain google.com in the default list');
    }

    public function testListDefaultSupportedIdpConfigs(): void
    {
        $defaultIdpsConfigs = $this->identityPlatform->listDefaultSupportedIdpConfigs();

        $this->assertContainsOnly(DefaultSupportedIdpConfig::class, $defaultIdpsConfigs, false, 'Should only contain valid Default Supported Idp Configs');
    }

    public function testCreateDefaultSupportedIdpConfigs(): void
    {
        $idpName = 'google.com';

        $idpConfig = $this->sampleDefaultIdpConfig($idpName);

        $idp = $this->identityPlatform->createDefaultSupportedIdpConfigs($idpConfig);

        $this->assertInstanceOf(DefaultSupportedIdpConfig::class, $idp);
        $this->assertEquals($idpConfig, $idp->toArray());

        $this->identityPlatform->deleteDefaultSupportedIdpConfigs($idpName);
    }


    public function testDeleteDefaultSupportedIdpConfigs(): void
    {
        $idpName = 'google.com';

        $idpConfig = $this->sampleDefaultIdpConfig($idpName);
        $idp = $this->identityPlatform->createDefaultSupportedIdpConfigs($idpConfig);
        $this->identityPlatform->deleteDefaultSupportedIdpConfigs($idpName);

        $this->expectException(ConfigurationNotFound::class);
        $idp = $this->identityPlatform->getDefaultSupportedIdpConfigs($idpName);
    }

    public function testGetDefaultSupportedIdpConfigs(): void
    {
        $idpName = 'google.com';
        $idpConfig = $this->sampleDefaultIdpConfig($idpName);
        $idp = $this->identityPlatform->createDefaultSupportedIdpConfigs($idpConfig);
        $idpRetrieved = $this->identityPlatform->getDefaultSupportedIdpConfigs($idpName);

        $this->assertEquals($idp->toArray(), $idpRetrieved->toArray());
        // cleanup
        $this->identityPlatform->deleteDefaultSupportedIdpConfigs('google.com');
    }


    public function testUpdateDefaultSupportedIdpConfigs(): void
    {
        $idpName = 'google.com';
        $idpConfig = $this->sampleDefaultIdpConfig();
        $idpConfig['enabled'] = false;

        $idp = $this->identityPlatform->createDefaultSupportedIdpConfigs($idpConfig);
        $this->assertNull($idp->toArray()['enabled']);
        $idpConfig['enabled'] = true;

        $idp = $this->identityPlatform->updateDefaultSupportedIdpConfigs($idpConfig['name'], $idpConfig);
        //Verify
        $this->assertTrue($idp->toArray()['enabled']);

        //Cleanup
        $this->identityPlatform->deleteDefaultSupportedIdpConfigs($idpName);
    }


    public function testCreateInboundSamlConfigs(): void
    {
        $idpName = 'saml.create';
        $idpConfig = $this->sampleInboundSamlConfig($idpName);
        $idp = $this->identityPlatform->createInboundSamlConfigs($idpConfig);

        //Verify
        $this->assertInstanceOf(InboundSamlConfig::class, $idp, 'Newly created response should be an instance of InboundSamlConfig');
        $retrieveIdp = $this->identityPlatform->getInboundSamlConfigs($idpName);
        $this->assertInstanceOf(InboundSamlConfig::class, $retrieveIdp, 'Retrieved Response should be an instance of InboundSamlConfig');

        //Cleanup
        $this->identityPlatform->deleteInboundSamlConfigs($idpName);
    }

    public function testDeleteInboundSamlConfigs(): void
    {
        $idpName = 'saml.delete';
        $idpConfig = $this->sampleInboundSamlConfig($idpName);
        $idp = $this->identityPlatform->createInboundSamlConfigs($idpConfig);

        //Execute
        $this->assertInstanceOf(InboundSamlConfig::class, $idp, 'Newly created response should be an instance of InboundSamlConfig');
        $this->identityPlatform->deleteInboundSamlConfigs($idpName);

        //Verify
        $this->expectException(ConfigurationNotFound::class);
        $this->identityPlatform->getInboundSamlConfigs($idpName);
    }

    public function testGetInboundSamlConfigs(): void
    {
        $idpName = 'saml.get';
        $idpConfig = $this->sampleInboundSamlConfig($idpName);
        $idp = $this->identityPlatform->createInboundSamlConfigs($idpConfig);

        //Verify
        $idpRetrieved = $this->identityPlatform->getInboundSamlConfigs($idpName);
        $this->assertEquals($idp->toArray(), $idpRetrieved->toArray());
        //Cleanup
        $this->identityPlatform->deleteInboundSamlConfigs($idpName);
    }

    public function testUpdateInboundSamlConfigs(): void
    {
        $idpName = 'saml.update';
        $idpConfig = $this->sampleInboundSamlConfig($idpName);
        $idp = $this->identityPlatform->createInboundSamlConfigs($idpConfig);


        $this->assertNull($idp->toArray()['enabled']);
        $idpRetrieved = $this->identityPlatform->getInboundSamlConfigs($idpName);
        $this->assertNull($idpRetrieved->toArray()['enabled'], 'Initial Enabled Value should not true');

        //Verify
        $idpConfig['enabled'] = true;
        $idpRetrieved = $this->identityPlatform->updateInboundSamlConfigs($idpName, $idpConfig);
        $this->assertTrue($idpRetrieved->toArray()['enabled'], 'Updated Enabled value should be true');

        //Cleanup
        $this->identityPlatform->deleteInboundSamlConfigs($idpName);
    }


    public function testCreateOauthIdpConfigs(): void
    {
        $idpName = 'oidc.create';
        $idpConfig = $this->sampleOauthIdpConfig($idpName);
        $idp = $this->identityPlatform->createOauthIdpConfigs($idpConfig);

        $this->assertInstanceOf(OAuthIdpConfig::class, $idp);

        $this->identityPlatform->deleteOauthIdpConfigs($idpName);
    }

    public function testDeleteOauthIdpConfigs(): void
    {
        $idpName = 'oidc.delete';
        $idpConfig = $this->sampleOauthIdpConfig($idpName);
        $idp = $this->identityPlatform->createOauthIdpConfigs($idpConfig);

        //Execute
        $this->assertInstanceOf(OAuthIdpConfig::class, $idp, 'Newly created response should be an instance of InboundSamlConfig');
        $this->identityPlatform->deleteOauthIdpConfigs($idpName);

        //Verify
        $this->expectException(ConfigurationNotFound::class);
        $this->identityPlatform->getOauthIdpConfigs($idpName);
    }


    public function testGetOauthIdpConfigs(): void
    {
        $idpName = 'oidc.get';
        $idpConfig = $this->sampleOauthIdpConfig($idpName);
        $idp = $this->identityPlatform->createOauthIdpConfigs($idpConfig);

        //Verify
        $idpRetrieved = $this->identityPlatform->getOauthIdpConfigs($idpName);
        $this->assertEquals($idp->toArray(), $idpRetrieved->toArray());

        //Cleanup
        $this->identityPlatform->deleteOauthIdpConfigs($idpName);
    }


    public function testUpdateOauthIdpConfigs() : void
    {
        $idpName = 'oidc.update';
        $idpConfig = $this->sampleOauthIdpConfig($idpName);
        $idp = $this->identityPlatform->createOauthIdpConfigs($idpConfig);


        $this->assertNull($idp->toArray()['enabled']);
        $idpRetrieved = $this->identityPlatform->getOauthIdpConfigs($idpName);
        $this->assertNull($idpRetrieved->toArray()['enabled'], 'Initial Enabled Value should not true');

        //Verify
        $idpConfig['enabled'] = true;
        $idpRetrieved = $this->identityPlatform->updateOauthIdpConfigs($idpName, $idpConfig);
        $this->assertTrue($idpRetrieved->toArray()['enabled'], 'Updated Enabled value should be true');

        //Cleanup
        $this->identityPlatform->deleteOauthIdpConfigs($idpName);
    }

    /**
     *
     * @param string $name
     * @return array<String, mixed>
     */
    protected function sampleDefaultIdpConfig(string $name = 'google.com') : array
    {
        return [
            'name'         => $name,
            'enabled'      => false,
            'clientId'     => $this->testClientId,
            'clientSecret' => $this->testClientSecret
        ];
    }
    /**
     * Undocumented function
     *
     * @param string $name
     * @return array<String, mixed>
     */
    protected function sampleInboundSamlConfig(string $name = 'saml.test') : array
    {
        return  [
            'name' => $name,
            'enabled' => false,
            'idpConfig' => [
                'idpEntityId' =>'http://google.com/coolid',
                'ssoUrl' => 'https://google.com',
                'idpCertificates' => [ ['x509Certificate' =>
                    '-----BEGIN CERTIFICATE-----
MIID2jCCA0MCAg39MA0GCSqGSIb3DQEBBQUAMIGbMQswCQYDVQQGEwJKUDEOMAwG
A1UECBMFVG9reW8xEDAOBgNVBAcTB0NodW8ta3UxETAPBgNVBAoTCEZyYW5rNERE
MRgwFgYDVQQLEw9XZWJDZXJ0IFN1cHBvcnQxGDAWBgNVBAMTD0ZyYW5rNEREIFdl
YiBDQTEjMCEGCSqGSIb3DQEJARYUc3VwcG9ydEBmcmFuazRkZC5jb20wHhcNMTIw
ODIyMDUyODAwWhcNMTcwODIxMDUyODAwWjBKMQswCQYDVQQGEwJKUDEOMAwGA1UE
CAwFVG9reW8xETAPBgNVBAoMCEZyYW5rNEREMRgwFgYDVQQDDA93d3cuZXhhbXBs
ZS5jb20wggIiMA0GCSqGSIb3DQEBAQUAA4ICDwAwggIKAoICAQCwvWITOLeyTbS1
Q/UacqeILIK16UHLvSymIlbbiT7mpD4SMwB343xpIlXN64fC0Y1ylT6LLeX4St7A
cJrGIV3AMmJcsDsNzgo577LqtNvnOkLH0GojisFEKQiREX6gOgq9tWSqwaENccTE
sAXuV6AQ1ST+G16s00iN92hjX9V/V66snRwTsJ/p4WRpLSdAj4272hiM19qIg9zr
h92e2rQy7E/UShW4gpOrhg2f6fcCBm+aXIga+qxaSLchcDUvPXrpIxTd/OWQ23Qh
vIEzkGbPlBA8J7Nw9KCyaxbYMBFb1i0lBjwKLjmcoihiI7PVthAOu/B71D2hKcFj
Kpfv4D1Uam/0VumKwhwuhZVNjLq1BR1FKRJ1CioLG4wCTr0LVgtvvUyhFrS+3PdU
R0T5HlAQWPMyQDHgCpbOHW0wc0hbuNeO/lS82LjieGNFxKmMBFF9lsN2zsA6Qw32
Xkb2/EFltXCtpuOwVztdk4MDrnaDXy9zMZuqFHpv5lWTbDVwDdyEQNclYlbAEbDe
vEQo/rAOZFl94Mu63rAgLiPeZN4IdS/48or5KaQaCOe0DuAb4GWNIQ42cYQ5TsEH
Wt+FIOAMSpf9hNPjDeu1uff40DOtsiyGeX9NViqKtttaHpvd7rb2zsasbcAGUl+f
NQJj4qImPSB9ThqZqPTukEcM/NtbeQIDAQABMA0GCSqGSIb3DQEBBQUAA4GBAIAi
gU3My8kYYniDuKEXSJmbVB+K1upHxWDA8R6KMZGXfbe5BRd8s40cY6JBYL52Tgqd
l8z5Ek8dC4NNpfpcZc/teT1WqiO2wnpGHjgMDuDL1mxCZNL422jHpiPWkWp3AuDI
c7tL1QjbfAUHAQYwmHkWgPP+T2wAv0pOt36GgMCM
-----END CERTIFICATE-----
'
                ]],
            'signRequest' => true
            ],

            'spConfig' => [

                'spEntityId' => 'testSp',
                'callbackUri' => 'https://google.com',

            ],
            'displayName' => 'Saml Test',
        ];
    }
    /**
     * @internal
     * @param string $name
     * @return array<String, mixed>
     */
    protected function sampleOauthIdpConfig(string $name = 'oidc.test') : array
    {
        return [
            'name'         => $name,
            'issuer'       => 'https://google.com/',
            'displayName'  => 'Oauth Test',
            'enabled'      => false,
            'clientId'     => $this->testClientId,
            'clientSecret' => $this->testClientSecret,
            'responseType' => ['idToken' => true],
        ];
    }

    protected function cleanup() : void
    {
        try {
            $this->identityPlatform->deleteDefaultSupportedIdpConfigs('google.com');
        } catch (IdentityPlatformException $e) {
        }

        try {
            $this->identityPlatform->deleteInboundSamlConfigs('saml.get');
        } catch (IdentityPlatformException $e) {
        }

        try {
            $this->identityPlatform->deleteInboundSamlConfigs('saml.create');
        } catch (IdentityPlatformException $e) {
        }

        try {
            $this->identityPlatform->deleteInboundSamlConfigs('saml.delete');
        } catch (IdentityPlatformException $e) {
        }

        try {
            $this->identityPlatform->deleteInboundSamlConfigs('saml.update');
        } catch (IdentityPlatformException $e) {
        }
        try {
            $this->identityPlatform->deleteOauthIdpConfigs('oidc.get');
        } catch (IdentityPlatformException $e) {
        }
        try {
            $this->identityPlatform->deleteOauthIdpConfigs('oidc.create');
        } catch (IdentityPlatformException $e) {
        }
        try {
            $this->identityPlatform->deleteOauthIdpConfigs('oidc.delete');
        } catch (IdentityPlatformException $e) {
        }
        try {
            $this->identityPlatform->deleteOauthIdpConfigs('oidc.update');
        } catch (IdentityPlatformException $e) {
        }
    }

    protected function setupIdentityPlatform() : IdentityPlatform
    {
        return self::$factory->createIdentityPlatform();
    }
}

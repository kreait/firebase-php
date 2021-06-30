<?php

declare(strict_types=1);

namespace Kreait\Firebase\Contract;

use Kreait\Firebase\IdentityPlatform\DefaultSupportedIdpConfig;
use Kreait\Firebase\IdentityPlatform\InboundSamlConfig;
use Kreait\Firebase\IdentityPlatform\OAuthIdpConfig;
use Kreait\Firebase\Exception;
use Psr\Http\Message\ResponseInterface;

use Kreait\Firebase\Request;

interface IdentityPlatform
{
    /**
     * List all default supported Idps.
     *
     * @return array<String>
     *
     * @throws Exception\FirebaseException
     * @throws Exception\IdentityPlatformException
     */
    public function listAllDefaultSupportedIdpConfigs() : array;

    /**
     * List Default Supported Idps
     *
     * @return array<DefaultSupportedIdpConfig>
     *
     * @throws Exception\FirebaseException
     * @throws Exception\IdentityPlatformException
     */
    public function listDefaultSupportedIdpConfigs() : array;

    /**
     * Create a default supported Idp configuration for an Identity Toolkit project.
     *
     * @param array<string, mixed>|Request\DefaultSupportedIdpConfig $properties
     * @return DefaultSupportedIdpConfig
     *
     * @throws Exception\IdentityPlatformException
     * @throws Exception\FirebaseException
     */

    public function createDefaultSupportedIdpConfigs($properties) : DefaultSupportedIdpConfig;
    /**
     * Delete a default supported Idp configuration for an Identity Toolkit project.
     *
     * @param string $name
     * @return ResponseInterface
     *
     * @throws Exception\IdentityPlatformException
     * @throws Exception\FirebaseException
     */

    public function deleteDefaultSupportedIdpConfigs(string $name) : ResponseInterface;
    /**
     * Retrieve a default supported Idp configuration for an Identity Toolkit project.
     *
     * @param string $name
     * @return DefaultSupportedIdpConfig
     *
     * @throws Exception\IdentityPlatformException
     * @throws Exception\FirebaseException
     */
    public function getDefaultSupportedIdpConfigs(string $name) : DefaultSupportedIdpConfig;

    /**
     * Update a default supported Idp configuration for an Identity Toolkit project.
     *
     * @param string $name
     * @param array<string, mixed>|Request\DefaultSupportedIdpConfig $properties
     * @return DefaultSupportedIdpConfig
     *
     * @throws Exception\IdentityPlatformException
     * @throws Exception\FirebaseException
     */
    public function updateDefaultSupportedIdpConfigs(string $name, $properties) : DefaultSupportedIdpConfig;

    /**
     * Create an inbound SAML configuration for an Identity Toolkit project.
     *
     * @param array<string, mixed>|Request\InboundSamlConfig $properties
     * @return InboundSamlConfig
     *
     * @throws Exception\IdentityPlatformException
     * @throws Exception\FirebaseException
     */
    public function createInboundSamlConfigs($properties) : InboundSamlConfig;
    /**
     * Delete an inbound SAML configuration for an Identity Toolkit project.
     *
     * @param string $name
     * @return ResponseInterface
     *
     * @throws Exception\IdentityPlatformException
     * @throws Exception\FirebaseException
     */
    public function deleteInboundSamlConfigs(string $name) : ResponseInterface;
    /**
     * Get an inbound SAML configuration for an Identity Toolkit project.
     *
     * @param string $name
     * @return InboundSamlConfig
     *
     * @throws Exception\IdentityPlatformException
     * @throws Exception\FirebaseException
     */
    public function getInboundSamlConfigs(string $name) : InboundSamlConfig;
    /**
     * Update an inbound SAML configuration for an Identity Toolkit project.
     *
     * @param string $name
     * @param array<string, mixed>|Request\InboundSamlConfig $properties
     * @return InboundSamlConfig
     */
    public function updateInboundSamlConfigs(string $name, $properties) : InboundSamlConfig;

    /**
     * Create an Oidc Idp configuration for an Identity Toolkit project.
     *
     * @param array<string, mixed>|Request\OAuthIdpConfig $properties
     * @return OAuthIdpConfig
     *
     * @throws Exception\IdentityPlatformException
     * @throws Exception\FirebaseException
     */

    public function createOauthIdpConfigs($properties) : OAuthIdpConfig;
    /**
     * Delete an Oidc Idp configuration for an Identity Toolkit project.
     *
     * @param string $name
     * @return ResponseInterface
     *
     * @throws Exception\IdentityPlatformException
     * @throws Exception\FirebaseException
     */
    public function deleteOauthIdpConfigs(string $name) : ResponseInterface;

    /**
     * Get an Oidc Idp configuration for an Identity Toolkit project.
     *
     * @param string $name
     * @return OAuthIdpConfig
     *
     * @throws Exception\IdentityPlatformException
     * @throws Exception\FirebaseException
     */
    public function getOauthIdpConfigs(string $name) : OAuthIdpConfig;

    /**
     * Update Oidc Idp configuration for an Identity Toolkit project.
     *
     * @param string $name
     * @param array<string, mixed>|Request\OAuthIdpConfig $properties
     * @return OAuthIdpConfig
     *
     * @throws Exception\IdentityPlatformException
     * @throws Exception\FirebaseException
     */
    public function updateOauthIdpConfigs(string $name, $properties) : OAuthIdpConfig;



    //TODO
    // public function finalizeMfaEnrollment() : ResponseInterface;
    // public function startMfaEnrollment(): ResponseInterface;
    // public function withdrawMfaEnrollment(): ResponseInterface;

    // public function startMfaSignIn(): ResponseInterface;
    // public function finalizeMfaSignIn(): ResponseInterface;
}

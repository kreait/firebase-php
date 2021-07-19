<?php

declare(strict_types=1);

namespace Kreait\Firebase\Contract;

use Kreait\Firebase\Exception;
use Kreait\Firebase\IdentityPlatform\DefaultSupportedIdpConfig;
use Kreait\Firebase\IdentityPlatform\InboundSamlConfig;
use Kreait\Firebase\IdentityPlatform\OAuthIdpConfig;
use Kreait\Firebase\Request;
use Psr\Http\Message\ResponseInterface;

interface IdentityPlatform
{
    /**
     * List all default supported Idps.
     *
     * @throws Exception\FirebaseException
     * @throws Exception\IdentityPlatformException
     *
     * @return array<String>
     */
    public function listAllDefaultSupportedIdpConfigs(): array;

    /**
     * List Default Supported Idps.
     *
     * @throws Exception\FirebaseException
     * @throws Exception\IdentityPlatformException
     *
     * @return array<DefaultSupportedIdpConfig>
     */
    public function listDefaultSupportedIdpConfigs(): array;

    /**
     * Create a default supported Idp configuration for an Identity Toolkit project.
     *
     * @param array<string, mixed>|Request\DefaultSupportedIdpConfig $properties
     *
     * @throws Exception\IdentityPlatformException
     * @throws Exception\FirebaseException
     */
    public function createDefaultSupportedIdpConfigs($properties): DefaultSupportedIdpConfig;

    /**
     * Delete a default supported Idp configuration for an Identity Toolkit project.
     *
     * @param string $name
     *
     * @throws Exception\IdentityPlatformException
     * @throws Exception\FirebaseException
     */
    public function deleteDefaultSupportedIdpConfigs(string $name): ResponseInterface;

    /**
     * Retrieve a default supported Idp configuration for an Identity Toolkit project.
     *
     * @throws Exception\IdentityPlatformException
     * @throws Exception\FirebaseException
     */
    public function getDefaultSupportedIdpConfigs(string $name): DefaultSupportedIdpConfig;

    /**
     * Update a default supported Idp configuration for an Identity Toolkit project.
     *
     * @param array<string, mixed>|Request\DefaultSupportedIdpConfig $properties
     *
     * @throws Exception\IdentityPlatformException
     * @throws Exception\FirebaseException
     */
    public function updateDefaultSupportedIdpConfigs(string $name, $properties): DefaultSupportedIdpConfig;

    /**
     * Create an inbound SAML configuration for an Identity Toolkit project.
     *
     * @param array<string, mixed>|Request\InboundSamlConfig $properties
     *
     * @throws Exception\IdentityPlatformException
     * @throws Exception\FirebaseException
     */
    public function createInboundSamlConfigs($properties): InboundSamlConfig;

    /**
     * Delete an inbound SAML configuration for an Identity Toolkit project.
     *
     * @throws Exception\IdentityPlatformException
     * @throws Exception\FirebaseException
     */
    public function deleteInboundSamlConfigs(string $name): ResponseInterface;

    /**
     * Get an inbound SAML configuration for an Identity Toolkit project.
     *
     * @throws Exception\IdentityPlatformException
     * @throws Exception\FirebaseException
     */
    public function getInboundSamlConfigs(string $name): InboundSamlConfig;

    /**
     * Update an inbound SAML configuration for an Identity Toolkit project.
     *
     * @param array<string, mixed>|Request\InboundSamlConfig $properties
     */
    public function updateInboundSamlConfigs(string $name, $properties): InboundSamlConfig;

    /**
     * Create an Oidc Idp configuration for an Identity Toolkit project.
     *
     * @param array<string, mixed>|Request\OAuthIdpConfig $properties
     *
     * @throws Exception\IdentityPlatformException
     * @throws Exception\FirebaseException
     */
    public function createOauthIdpConfigs($properties): OAuthIdpConfig;

    /**
     * Delete an Oidc Idp configuration for an Identity Toolkit project.
     *
     * @throws Exception\IdentityPlatformException
     * @throws Exception\FirebaseException
     */
    public function deleteOauthIdpConfigs(string $name): ResponseInterface;

    /**
     * Get an Oidc Idp configuration for an Identity Toolkit project.
     *
     * @throws Exception\IdentityPlatformException
     * @throws Exception\FirebaseException
     */
    public function getOauthIdpConfigs(string $name): OAuthIdpConfig;

    /**
     * Update Oidc Idp configuration for an Identity Toolkit project.
     *
     * @param array<string, mixed>|Request\OAuthIdpConfig $properties
     *
     * @throws Exception\IdentityPlatformException
     * @throws Exception\FirebaseException
     */
    public function updateOauthIdpConfigs(string $name, $properties): OAuthIdpConfig;

    //TODO
    // public function finalizeMfaEnrollment() : ResponseInterface;
    // public function startMfaEnrollment(): ResponseInterface;
    // public function withdrawMfaEnrollment(): ResponseInterface;

    // public function startMfaSignIn(): ResponseInterface;
    // public function finalizeMfaSignIn(): ResponseInterface;
}

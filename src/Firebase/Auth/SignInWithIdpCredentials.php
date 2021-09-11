<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

final class SignInWithIdpCredentials implements IsTenantAware, SignIn
{
    private string $provider;
    private ?string $accessToken = null;
    private ?string $idToken = null;
    private ?string $linkingIdToken = null;
    private ?string $oauthTokenSecret = null;
    private ?string $rawNonce = null;
    private string $requestUri = 'http://localhost';
    private ?TenantId $tenantId = null;

    private function __construct(string $provider)
    {
        $this->provider = $provider;
    }

    public static function withAccessToken(string $provider, string $accessToken): self
    {
        $instance = new self($provider);
        $instance->accessToken = $accessToken;

        return $instance;
    }

    public static function withAccessTokenAndOauthTokenSecret(string $provider, string $accessToken, string $oauthTokenSecret): self
    {
        $instance = self::withAccessToken($provider, $accessToken);
        $instance->oauthTokenSecret = $oauthTokenSecret;

        return $instance;
    }

    public static function withIdToken(string $provider, string $idToken): self
    {
        $instance = new self($provider);
        $instance->idToken = $idToken;

        return $instance;
    }

    public function withRawNonce(string $rawNonce): self
    {
        $instance = clone $this;
        $instance->rawNonce = $rawNonce;

        return $instance;
    }

    public function withLinkingIdToken(string $idToken): self
    {
        $instance = clone $this;
        $instance->linkingIdToken = $idToken;

        return $instance;
    }

    public function withRequestUri(string $requestUri): self
    {
        $instance = clone $this;
        $instance->requestUri = $requestUri;

        return $instance;
    }

    public function withTenantId(TenantId $tenantId): self
    {
        $action = clone $this;
        $action->tenantId = $tenantId;

        return $action;
    }

    public function provider(): string
    {
        return $this->provider;
    }

    public function oauthTokenSecret(): ?string
    {
        return $this->oauthTokenSecret;
    }

    public function accessToken(): ?string
    {
        return $this->accessToken;
    }

    public function idToken(): ?string
    {
        return $this->idToken;
    }

    public function rawNonce(): ?string
    {
        return $this->rawNonce;
    }

    public function linkingIdToken(): ?string
    {
        return $this->linkingIdToken;
    }

    public function requestUri(): string
    {
        return $this->requestUri;
    }

    public function tenantId(): ?TenantId
    {
        return $this->tenantId;
    }
}

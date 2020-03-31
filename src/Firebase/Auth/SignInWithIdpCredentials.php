<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

final class SignInWithIdpCredentials implements SignIn
{
    /** @var string|null */
    private $accessToken;

    /** @var string|null */
    private $idToken;

    /** @var string */
    private $provider;

    /** @var string */
    private $requestUri = 'http://localhost';

    private function __construct()
    {
    }

    public static function withAccessToken(string $provider, string $accessToken): self
    {
        $instance = new self();
        $instance->provider = $provider;
        $instance->accessToken = $accessToken;

        return $instance;
    }

    public static function withIdToken(string $provider, string $idToken): self
    {
        $instance = new self();
        $instance->provider = $provider;
        $instance->idToken = $idToken;

        return $instance;
    }

    public function withRequestUri(string $requestUri): self
    {
        $instance = clone $this;
        $instance->requestUri = $requestUri;

        return $instance;
    }

    public function provider(): string
    {
        return $this->provider;
    }

    public function accessToken(): ?string
    {
        return $this->accessToken;
    }

    public function idToken(): ?string
    {
        return $this->idToken;
    }

    public function requestUri(): string
    {
        return $this->requestUri;
    }
}

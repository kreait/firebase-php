<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

final class SignInResult
{
    /** @var string|null */
    private $idToken;

    /** @var string|null */
    private $accessToken;

    /** @var string|null */
    private $refreshToken;

    /** @var int|null */
    private $ttl;

    /** @var array */
    private $data = [];

    private function __construct()
    {
    }

    public static function fromData(array $data): self
    {
        $instance = new self();

        if ($expiresIn = $data['expiresIn'] ?? $data['expires_in'] ?? null) {
            $instance->ttl = (int) $expiresIn;
        }

        $instance->idToken = $data['idToken'] ?? $data['id_token'] ?? null;
        $instance->accessToken = $data['accessToken'] ?? $data['access_token'] ?? null;
        $instance->refreshToken = $data['refreshToken'] ?? $data['refresh_token'] ?? null;
        $instance->data = $data;

        return $instance;
    }

    /**
     * @return string|null
     */
    public function idToken()
    {
        return $this->idToken;
    }

    /**
     * @return string|null
     */
    public function accessToken()
    {
        return $this->accessToken;
    }

    /**
     * @return string|null
     */
    public function refreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * @return int|null
     */
    public function ttl()
    {
        return $this->ttl;
    }

    public function data(): array
    {
        return $this->data;
    }

    public function asTokenResponse(): array
    {
        return [
            'token_type' => 'Bearer',
            'access_token' => $this->accessToken(),
            'id_token' => $this->idToken,
            'refresh_token' => $this->refreshToken(),
            'expires_in' => $this->ttl(),
        ];
    }
}

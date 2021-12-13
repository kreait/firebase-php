<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\UnencryptedToken;

final class SignInResult
{
    private ?string $idToken = null;
    private ?string $accessToken = null;
    private ?string $refreshToken = null;
    private ?int $ttl = null;
    /** @var array<string, mixed> */
    private array $data = [];
    private ?string $firebaseUserId = null;
    private ?string $tenantId = null;

    private Configuration $config;

    private function __construct()
    {
        $this->config = Configuration::forUnsecuredSigner();
    }

    /**
     * @param array<string, mixed> $data
     */
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

    public function idToken(): ?string
    {
        return $this->idToken;
    }

    public function firebaseUserId(): ?string
    {
        // @codeCoverageIgnoreStart
        if ($this->firebaseUserId) {
            return $this->firebaseUserId;
        }
        // @codeCoverageIgnoreEnd

        if ($this->idToken) {
            $idToken = $this->config->parser()->parse($this->idToken);
            \assert($idToken instanceof UnencryptedToken);

            foreach (['sub', 'localId', 'user_id'] as $claim) {
                if ($uid = $idToken->claims()->get($claim, false)) {
                    return $this->firebaseUserId = $uid;
                }
            }
        }

        return null;
    }

    public function firebaseTenantId(): ?string
    {
        if ($this->tenantId) {
            return $this->tenantId;
        }

        if ($this->idToken) {
            $idToken = $this->config->parser()->parse($this->idToken);
            \assert($idToken instanceof UnencryptedToken);

            $firebaseClaims = $idToken->claims()->get('firebase', new \stdClass());

            if (\is_object($firebaseClaims) && \property_exists($firebaseClaims, 'tenant')) {
                return $this->tenantId = $firebaseClaims->tenant;
            }

            if (\is_array($firebaseClaims) && \array_key_exists('tenant', $firebaseClaims)) {
                return $this->tenantId = $firebaseClaims['tenant'];
            }
        }

        return null;
    }

    public function accessToken(): ?string
    {
        return $this->accessToken;
    }

    public function refreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function ttl(): ?int
    {
        return $this->ttl;
    }

    /**
     * @return array<string, mixed>
     */
    public function data(): array
    {
        return $this->data;
    }

    /**
     * @return array<string, mixed>
     */
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

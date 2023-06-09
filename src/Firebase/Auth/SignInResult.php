<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use Kreait\Firebase\JWT\Token\Parser;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\UnencryptedToken;
use stdClass;

use function array_key_exists;
use function assert;
use function is_array;
use function is_object;
use function property_exists;

final class SignInResult
{
    /**
     * @var non-empty-string|null
     */
    private ?string $idToken = null;

    /**
     * @var non-empty-string|null
     */
    private ?string $accessToken = null;

    /**
     * @var non-empty-string|null
     */
    private ?string $refreshToken = null;

    /**
     * @var positive-int|null
     */
    private ?int $ttl = null;

    /**
     * @var array<non-empty-string, mixed>
     */
    private array $data = [];

    /**
     * @var non-empty-string|null
     */
    private ?string $firebaseUserId = null;

    /**
     * @var non-empty-string|null
     */
    private ?string $tenantId = null;
    private readonly Parser $parser;

    private function __construct()
    {
        $this->parser = new Parser(new JoseEncoder());
    }

    /**
     * @param array<non-empty-string, mixed> $data
     */
    public static function fromData(array $data): self
    {
        $instance = new self();

        $expiresIn = (int) ($data['expiresIn'] ?? $data['expires_in'] ?? null);

        if ($expiresIn > 0) {
            $instance->ttl = $expiresIn;
        }

        $instance->idToken = $data['idToken'] ?? $data['id_token'] ?? null;
        $instance->accessToken = $data['accessToken'] ?? $data['access_token'] ?? null;
        $instance->refreshToken = $data['refreshToken'] ?? $data['refresh_token'] ?? null;
        $instance->data = $data;

        return $instance;
    }

    /**
     * @return non-empty-string|null
     */
    public function idToken(): ?string
    {
        return $this->idToken;
    }

    /**
     * @return non-empty-string|null
     */
    public function firebaseUserId(): ?string
    {
        if ($this->firebaseUserId) {
            return $this->firebaseUserId;
        }

        if ($this->idToken) {
            $idToken = $this->parser->parse($this->idToken);
            assert($idToken instanceof UnencryptedToken);

            foreach (['sub', 'localId', 'user_id'] as $claim) {
                if ($uid = $idToken->claims()->get($claim, false)) {
                    return $this->firebaseUserId = $uid;
                }
            }
        }

        if ($localId = $this->data['localId'] ?? null) {
            return $this->firebaseUserId = $localId;
        }

        return null;
    }

    /**
     * @return non-empty-string|null
     */
    public function firebaseTenantId(): ?string
    {
        if ($this->tenantId) {
            return $this->tenantId;
        }

        if ($this->idToken) {
            $idToken = $this->parser->parse($this->idToken);
            assert($idToken instanceof UnencryptedToken);

            $firebaseClaims = $idToken->claims()->get('firebase', new stdClass());

            if (is_object($firebaseClaims) && property_exists($firebaseClaims, 'tenant')) {
                return $this->tenantId = $firebaseClaims->tenant;
            }

            if (is_array($firebaseClaims) && array_key_exists('tenant', $firebaseClaims)) {
                return $this->tenantId = $firebaseClaims['tenant'];
            }
        }

        return null;
    }

    /**
     * @return non-empty-string|null
     */
    public function accessToken(): ?string
    {
        return $this->accessToken;
    }

    /**
     * @return non-empty-string|null
     */
    public function refreshToken(): ?string
    {
        return $this->refreshToken;
    }

    /**
     * @return positive-int|null
     */
    public function ttl(): ?int
    {
        return $this->ttl;
    }

    /**
     * @return array<non-empty-string, mixed>
     */
    public function data(): array
    {
        return $this->data;
    }

    /**
     * @return array<non-empty-string, mixed>
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

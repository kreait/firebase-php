<?php

declare(strict_types=1);

namespace Kreait\Firebase\AppCheck;

use InvalidArgumentException;
use JsonSerializable;

use function array_key_exists;

/**
 * @phpstan-type AppCheckTokenShape array{
 *     token: string,
 *     ttl: string
 * }
 */
final class AppCheckToken implements JsonSerializable
{
    private string $token;
    private string $ttl;

    private function __construct(string $token, string $ttl)
    {
        $this->token = $token;
        $this->ttl = $ttl;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (!array_key_exists('token', $data)) {
            throw new InvalidArgumentException('The "token" key is missing from the token data.');
        }

        if (!array_key_exists('ttl', $data)) {
            throw new InvalidArgumentException('The "ttl" key is missing from the token data.');
        }

        return new self(
            $data['token'],
            $data['ttl'],
        );
    }

    public function token(): string
    {
        return $this->token;
    }

    public function ttl(): string
    {
        return $this->ttl;
    }

    /**
     * @return AppCheckTokenShape
     */
    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'ttl' => $this->ttl,
        ];
    }

    /**
     * @return AppCheckTokenShape
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}

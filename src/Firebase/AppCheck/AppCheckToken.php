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
final class AppCheckToken
{
    private function __construct(
        private string $token,
        private string $ttl,
    ) {
    }

    /**
     * @param AppCheckTokenShape $data
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
}

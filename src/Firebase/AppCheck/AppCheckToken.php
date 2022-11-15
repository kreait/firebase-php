<?php

declare(strict_types=1);

namespace Kreait\Firebase\AppCheck;

use InvalidArgumentException;

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
        public readonly string $token,
        public readonly string $ttl,
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
}

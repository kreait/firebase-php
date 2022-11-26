<?php

declare(strict_types=1);

namespace Kreait\Firebase\AppCheck;

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
        return new self($data['token'], $data['ttl']);
    }
}

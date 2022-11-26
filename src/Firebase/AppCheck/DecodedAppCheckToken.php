<?php

declare(strict_types=1);

namespace Kreait\Firebase\AppCheck;

/**
 * @phpstan-type DecodedAppCheckTokenShape array{
 *     app_id?: non-empty-string,
 *     aud: array<string>,
 *     exp: int,
 *     iat: int,
 *     iss: non-empty-string,
 *     sub: non-empty-string,
 * }
 */
final class DecodedAppCheckToken
{
    /**
     * @param non-empty-string $app_id
     * @param array<string> $aud
     * @param non-empty-string $sub
     */
    private function __construct(
        public readonly string $app_id,
        public readonly array $aud,
        public readonly int $exp,
        public readonly int $iat,
        public readonly string $iss,
        public readonly string $sub,
    ) {
    }

    /**
     * @param DecodedAppCheckTokenShape $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['app_id'] ?? $data['sub'],
            $data['aud'],
            $data['exp'],
            $data['iat'],
            $data['iss'],
            $data['sub'],
        );
    }
}

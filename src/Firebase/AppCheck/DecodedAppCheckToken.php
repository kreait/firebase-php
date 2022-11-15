<?php

declare(strict_types=1);

namespace Kreait\Firebase\AppCheck;

/**
 * @phpstan-type DecodedAppCheckTokenShape array{
 *     app_id: non-empty-string,
 *     aud: array<string>,
 *     exp: string,
 *     iat: string,
 *     iss: string,
 *     sub: string,
 * }
 */
final class DecodedAppCheckToken
{
    /**
     * @param non-empty-string $app_id
     * @param array<string> $aud
     */
    private function __construct(
        public readonly string $app_id,
        public readonly array $aud,
        public readonly string $exp,
        public readonly string $iat,
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
            $data['app_id'],
            $data['aud'],
            $data['exp'],
            $data['iat'],
            $data['iss'],
            $data['sub'],
        );
    }

    /**
     * @return DecodedAppCheckTokenShape
     */
    public function toArray(): array
    {
        return [
            'app_id' => $this->app_id,
            'aud' => $this->aud,
            'exp' => $this->exp,
            'iat' => $this->iat,
            'iss' => $this->iss,
            'sub' => $this->sub,
        ];
    }
}

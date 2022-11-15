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
        private string $app_id,
        private array $aud,
        private string $exp,
        private string $iat,
        private string $iss,
        private string $sub,
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

    /** @return non-empty-string */
    public function app_id(): string
    {
        return $this->app_id;
    }

    /**
     * @return array<string>
     */
    public function aud(): array
    {
        return $this->aud;
    }

    public function exp(): string
    {
        return $this->exp;
    }

    public function iat(): string
    {
        return $this->iat;
    }

    public function iss(): string
    {
        return $this->iss;
    }

    public function sub(): string
    {
        return $this->sub;
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

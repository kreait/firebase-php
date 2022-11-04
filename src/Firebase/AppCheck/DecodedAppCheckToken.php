<?php

declare(strict_types=1);

namespace Kreait\Firebase\AppCheck;

use InvalidArgumentException;
use JsonSerializable;

use function array_key_exists;

/**
 * @phpstan-type DecodedAppCheckTokenShape array{
 *     app_id: string,
 *     aud: array<string>,
 *     exp: string,
 *     iat: string,
 *     iss: string,
 *     sub: string,
 * }
 */
final class DecodedAppCheckToken implements JsonSerializable
{
    private string $app_id;

    /** @var array<string> */
    private array $aud;
    private string $exp;
    private string $iat;
    private string $iss;
    private string $sub;

    /**
     * @param array<string> $aud
     */
    private function __construct(
        string $app_id,
        array $aud,
        string $exp,
        string $iat,
        string $iss,
        string $sub,
    ) {
        $this->app_id = $app_id;
        $this->aud = $aud;
        $this->exp = $exp;
        $this->iat = $iat;
        $this->iss = $iss;
        $this->sub = $sub;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (!array_key_exists('app_id', $data)) {
            throw new InvalidArgumentException('The "app_id" key is missing from the token data.');
        }

        if (!array_key_exists('aud', $data)) {
            throw new InvalidArgumentException('The "app_id" key is missing from the token data.');
        }

        if (!array_key_exists('exp', $data)) {
            throw new InvalidArgumentException('The "app_id" key is missing from the token data.');
        }

        if (!array_key_exists('iat', $data)) {
            throw new InvalidArgumentException('The "app_id" key is missing from the token data.');
        }

        if (!array_key_exists('iss', $data)) {
            throw new InvalidArgumentException('The "app_id" key is missing from the token data.');
        }

        if (!array_key_exists('sub', $data)) {
            throw new InvalidArgumentException('The "app_id" key is missing from the token data.');
        }

        return new self(
            $data['app_id'],
            $data['aud'],
            $data['exp'],
            $data['iat'],
            $data['iss'],
            $data['sub'],
        );
    }

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

    /**
     * @return DecodedAppCheckTokenShape
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}

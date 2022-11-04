<?php

declare(strict_types=1);

namespace Kreait\Firebase\AppCheck;

use InvalidArgumentException;
use JsonSerializable;

use function array_key_exists;

/**
 * @phpstan-type AppCheckTokenOptionsShape array{
 *     ttl: string|null,
 * }
 */
final class AppCheckTokenOptions implements JsonSerializable
{
    private ?string $ttl;

    private function __construct(?string $ttl = null)
    {
        $this->ttl = $ttl;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (!array_key_exists('ttl', $data)) {
            throw new InvalidArgumentException('The "app_id" key is missing from the token data.');
        }

        return new self(
            $data['ttl'],
        );
    }

    public function ttl(): ?string
    {
        return $this->ttl();
    }

    /**
     * @return AppCheckTokenOptionsShape
     */
    public function toArray(): array
    {
        return [
            'ttl' => $this->ttl,
        ];
    }

    /**
     * @return AppCheckTokenOptionsShape
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}

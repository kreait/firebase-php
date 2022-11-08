<?php

declare(strict_types=1);

namespace Kreait\Firebase\AppCheck;

use JsonSerializable;
use Kreait\Firebase\Exception\AppCheck\InvalidAppCheckTokenOptions;

use function is_numeric;

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
     * @param AppCheckTokenOptionsShape $data
     *
     * @throws InvalidAppCheckTokenOptions
     */
    public static function fromArray(array $data): self
    {
        $ttl = $data['ttl'] ?? null;

        if (null === $ttl) {
            return new self();
        }

        if (!is_numeric($ttl)) {
            throw new InvalidAppCheckTokenOptions('The ttl must be a number.');
        }

        if ($ttl < 1800 || $ttl > 604800) {
            throw new InvalidAppCheckTokenOptions('The ttl must be a duration between 30 minutes and 7 days.');
        }

        return new self($ttl);
    }

    public function ttl(): ?string
    {
        return $this->ttl;
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

<?php

declare(strict_types=1);

namespace Kreait\Firebase\AppCheck;

use Kreait\Firebase\Exception\AppCheck\InvalidAppCheckTokenOptions;

/**
 * @phpstan-type AppCheckTokenOptionsShape array{
 *     ttl: int|null,
 * }
 */
final class AppCheckTokenOptions
{
    private function __construct(
        public readonly ?int $ttl = null,
    ) {
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

        if ($ttl < 1800 || $ttl > 604800) {
            throw new InvalidAppCheckTokenOptions('The ttl must be a duration between 30 minutes and 7 days.');
        }

        return new self($ttl);
    }
}

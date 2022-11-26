<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use DateTimeImmutable;
use Kreait\Firebase\Util\DT;

use function array_key_exists;

/**
 * @phpstan-type UserMetadataResponseShape array{
 *     createdAt: non-empty-string,
 *     lastLoginAt?: non-empty-string,
 *     passwordUpdatedAt?: non-empty-string,
 *     lastRefreshAt?: non-empty-string
 * }
 */
final class UserMetaData
{
    public function __construct(
        public readonly DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $lastLoginAt,
        public readonly ?DateTimeImmutable $passwordUpdatedAt,
        public readonly ?DateTimeImmutable $lastRefreshAt,
    ) {
    }

    /**
     * @internal
     *
     * @param UserMetadataResponseShape $data
     */
    public static function fromResponseData(array $data): self
    {
        $createdAt = DT::toUTCDateTimeImmutable($data['createdAt']);

        $lastLoginAt = array_key_exists('lastLoginAt', $data)
            ? DT::toUTCDateTimeImmutable($data['lastLoginAt'])
            : null;

        $passwordUpdatedAt = array_key_exists('passwordUpdatedAt', $data)
            ? DT::toUTCDateTimeImmutable($data['passwordUpdatedAt'])
            : null;

        $lastRefreshAt = array_key_exists('lastRefreshAt', $data)
            ? DT::toUTCDateTimeImmutable($data['lastRefreshAt'])
            : null;

        return new self($createdAt, $lastLoginAt, $passwordUpdatedAt, $lastRefreshAt);
    }
}

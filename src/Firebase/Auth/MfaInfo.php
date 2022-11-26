<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use DateTimeImmutable;
use Kreait\Firebase\Util\DT;

use function array_key_exists;

/**
 * @phpstan-type MfaInfoResponseShape array{
 *     mfaEnrollmentId: non-empty-string,
 *     displayName?: non-empty-string,
 *     phoneInfo?: non-empty-string,
 *     enrolledAt?: non-empty-string
 * }
 */
final class MfaInfo
{
    private function __construct(
        public readonly string $mfaEnrollmentId,
        public readonly ?string $displayName,
        public readonly ?string $phoneInfo,
        public readonly ?DateTimeImmutable $enrolledAt,
    ) {
    }

    /**
     * @internal
     *
     * @param MfaInfoResponseShape $data
     */
    public static function fromResponseData(array $data): self
    {
        $enrolledAt = array_key_exists('enrolledAt', $data)
            ? DT::toUTCDateTimeImmutable($data['enrolledAt'])
            : null;

        return new self(
            $data['mfaEnrollmentId'],
            $data['displayName'] ?? null,
            $data['phoneInfo'] ?? null,
            $enrolledAt,
        );
    }
}

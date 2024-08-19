<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use Beste\Json;
use DateTimeImmutable;
use Kreait\Firebase\Util\DT;

use function array_key_exists;
use function array_map;

/**
 * @phpstan-import-type ProviderUserInfoResponseShape from UserInfo
 * @phpstan-import-type UserMetadataResponseShape from UserMetaData
 * @phpstan-import-type MfaInfoResponseShape from MfaInfo
 *
 * @phpstan-type UserRecordResponseShape array{
 *     localId: non-empty-string,
 *     email?: non-empty-string,
 *     emailVerified?: bool,
 *     displayName?: non-empty-string,
 *     photoUrl?: non-empty-string,
 *     phoneNumber?: non-empty-string,
 *     disabled?: bool,
 *     passwordHash?: non-empty-string,
 *     salt?: non-empty-string,
 *     customAttributes?: non-empty-string,
 *     tenantId?: non-empty-string,
 *     providerUserInfo?: list<ProviderUserInfoResponseShape>,
 *     mfaInfo?: list<MfaInfoResponseShape>,
 *     createdAt: non-empty-string,
 *     lastLoginAt?: non-empty-string,
 *     passwordUpdatedAt?: non-empty-string,
 *     lastRefreshAt?: non-empty-string,
 *     validSince?: non-empty-string
 * }
 */
final class UserRecord
{
    /**
     * @param non-empty-string $uid
     * @param non-empty-string|null $email
     * @param non-empty-string|null $displayName
     * @param non-empty-string|null $phoneNumber
     * @param non-empty-string|null $photoUrl
     * @param list<UserInfo> $providerData
     * @param non-empty-string|null $passwordHash
     * @param non-empty-string|null $passwordSalt
     * @param array<non-empty-string, mixed> $customClaims
     * @param non-empty-string|null $tenantId
     */
    public function __construct(
        public readonly string $uid,
        public readonly ?string $email,
        public readonly bool $emailVerified,
        public readonly ?string $displayName,
        public readonly ?string $phoneNumber,
        public readonly ?string $photoUrl,
        public readonly bool $disabled,
        public readonly UserMetaData $metadata,
        public readonly array $providerData,
        public readonly ?MfaInfo $mfaInfo,
        public readonly ?string $passwordHash,
        public readonly ?string $passwordSalt,
        public readonly array $customClaims,
        public readonly ?string $tenantId,
        public readonly ?DateTimeImmutable $tokensValidAfterTime,
    ) {
    }

    /**
     * @internal
     *
     * @param UserRecordResponseShape $data
     */
    public static function fromResponseData(array $data): self
    {
        $validSince = array_key_exists('validSince', $data)
            ? DT::toUTCDateTimeImmutable($data['validSince'])
            : null;

        $customClaims = array_key_exists('customAttributes', $data)
            ? Json::decode($data['customAttributes'], true)
            : [];

        $providerUserInfo = array_key_exists('providerUserInfo', $data)
            ? self::userInfoFromResponseData($data)
            : [];

        return new self(
            $data['localId'],
            $data['email'] ?? null,
            $data['emailVerified'] ?? false,
            $data['displayName'] ?? null,
            $data['phoneNumber'] ?? null,
            $data['photoUrl'] ?? null,
            $data['disabled'] ?? false,
            self::userMetaDataFromResponseData($data),
            $providerUserInfo,
            self::mfaInfoFromResponseData($data),
            $data['passwordHash'] ?? null,
            $data['salt'] ?? null,
            $customClaims,
            $data['tenantId'] ?? null,
            $validSince,
        );
    }

    /**
     * @param UserMetadataResponseShape $data
     */
    private static function userMetaDataFromResponseData(array $data): UserMetaData
    {
        return UserMetaData::fromResponseData($data);
    }

    /**
     * @param array{
     *     mfaInfo?: list<MfaInfoResponseShape>
     * } $data
     */
    private static function mfaInfoFromResponseData(array $data): ?MfaInfo
    {
        if (!array_key_exists('mfaInfo', $data)) {
            return null;
        }

        $mfaInfo = array_shift($data['mfaInfo']);

        if ($mfaInfo === null) {
            return null;
        }

        return MfaInfo::fromResponseData($mfaInfo);
    }

    /**
     * @param array{providerUserInfo: list<ProviderUserInfoResponseShape>} $data
     *
     * @return list<UserInfo>
     */
    private static function userInfoFromResponseData(array $data): array
    {
        return array_map(
            static fn(array $userInfoData): UserInfo => UserInfo::fromResponseData($userInfoData),
            $data['providerUserInfo'],
        );
    }
}

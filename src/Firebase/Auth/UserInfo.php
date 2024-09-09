<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

/**
 * Represents a user's info from a third-party identity provider
 * such as Google or Facebook.
 *
 * @phpstan-type UserInfoShape array{
 *     uid: non-empty-string,
 *     providerId: non-empty-string,
 *     displayName?: non-empty-string,
 *     email?: non-empty-string,
 *     photoUrl?: non-empty-string,
 *     phoneNumber?: non-empty-string
 *
 * }
 * @phpstan-type ProviderUserInfoResponseShape array{
 *     rawId: non-empty-string,
 *     providerId: non-empty-string,
 *     displayName?: non-empty-string,
 *     email?: non-empty-string,
 *     federatedId?: non-empty-string,
 *     photoUrl?: non-empty-string,
 *     phoneNumber?: non-empty-string
 * }
 */
final class UserInfo
{
    /**
     * @param non-empty-string $uid
     * @param non-empty-string $providerId
     * @param non-empty-string|null $displayName
     * @param non-empty-string|null $email
     * @param non-empty-string|null $phoneNumber
     * @param non-empty-string|null $photoUrl
     */
    public function __construct(
        public readonly string $uid,
        public readonly string $providerId,
        public readonly ?string $displayName,
        public readonly ?string $email,
        public readonly ?string $phoneNumber,
        public readonly ?string $photoUrl,
    ) {
    }

    /**
     * @internal
     *
     * @param ProviderUserInfoResponseShape $data
     */
    public static function fromResponseData(array $data): self
    {
        return new self(
            $data['rawId'],
            $data['providerId'],
            $data['displayName'] ?? null,
            $data['email'] ?? null,
            $data['phoneNumber'] ?? null,
            $data['photoUrl'] ?? null,
        );
    }
}

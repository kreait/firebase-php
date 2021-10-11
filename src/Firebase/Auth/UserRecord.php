<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use DateTimeImmutable;
use Kreait\Firebase\Util\Deprecation;
use Kreait\Firebase\Util\DT;
use Kreait\Firebase\Util\JSON;

/**
 * @property array<string, mixed> $customAttributes Deprecated, use {@see UserRecord::$customClaims} instead
 */
class UserRecord implements \JsonSerializable
{
    public string $uid;
    public bool $emailVerified = false;
    public bool $disabled = false;
    public UserMetaData $metadata;
    public ?string $email = null;
    public ?string $displayName = null;
    public ?string $photoUrl = null;
    public ?string $phoneNumber = null;
    /** @var UserInfo[] */
    public array $providerData = [];
    public ?string $passwordHash = null;
    public ?string $passwordSalt = null;
    /** @var array<string, mixed> */
    public array $customClaims = [];
    public ?DateTimeImmutable $tokensValidAfterTime = null;
    public ?string $tenantId = null;

    public function __construct()
    {
        $this->metadata = new UserMetaData();
        $this->uid = '';
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromResponseData(array $data): self
    {
        $record = new self();
        $record->uid = $data['localId'] ?? '';
        $record->email = $data['email'] ?? null;
        $record->emailVerified = $data['emailVerified'] ?? $record->emailVerified;
        $record->displayName = $data['displayName'] ?? null;
        $record->photoUrl = $data['photoUrl'] ?? null;
        $record->phoneNumber = $data['phoneNumber'] ?? null;
        $record->disabled = $data['disabled'] ?? $record->disabled;
        $record->metadata = self::userMetaDataFromResponseData($data);
        $record->providerData = self::userInfoFromResponseData($data);
        $record->passwordHash = $data['passwordHash'] ?? null;
        $record->passwordSalt = $data['salt'] ?? null;
        $record->tenantId = $data['tenantId'] ?? $data['tenant_id'] ?? null;

        if ($data['validSince'] ?? null) {
            $record->tokensValidAfterTime = DT::toUTCDateTimeImmutable($data['validSince']);
        }

        if ($customClaims = $data['customClaims'] ?? $data['customAttributes'] ?? '{}') {
            $record->customClaims = JSON::decode($customClaims, true);
        }

        return $record;
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function userMetaDataFromResponseData(array $data): UserMetaData
    {
        return UserMetaData::fromResponseData($data);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<int, UserInfo>
     */
    private static function userInfoFromResponseData(array $data): array
    {
        return \array_map(
            static fn (array $userInfoData) => UserInfo::fromResponseData($userInfoData),
            $data['providerUserInfo'] ?? []
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $data = \get_object_vars($this);

        $data['tokensValidAfterTime'] = $this->tokensValidAfterTime !== null
            ? $this->tokensValidAfterTime->format(DATE_ATOM)
            : null;

        return $data;
    }

    /**
     * @return mixed
     */
    public function __get(string $name)
    {
        if (\mb_strtolower($name) === 'customattributes') {
            Deprecation::trigger(__CLASS__.'::customAttributes', __CLASS__.'::customClaims');

            return $this->customClaims;
        }

        return $this->{$name};
    }
}

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
    /** @var string */
    public $uid;

    /** @var string|null */
    public $email;

    /** @var bool */
    public $emailVerified = false;

    /** @var string|null */
    public $displayName;

    /** @var string|null */
    public $photoUrl;

    /** @var string|null */
    public $phoneNumber;

    /** @var bool */
    public $disabled;

    /** @var UserMetaData */
    public $metadata;

    /** @var UserInfo[] */
    public $providerData;

    /** @var string|null */
    public $passwordHash;

    /** @var string|null */
    public $passwordSalt;

    /** @var array<string, mixed> */
    public $customClaims;

    /** @var DateTimeImmutable|null */
    public $tokensValidAfterTime;

    /** @var string|null */
    public $tenantId;

    /**
     * @param array<string, mixed> $data
     */
    public static function fromResponseData(array $data): self
    {
        $record = new self();
        $record->uid = $data['localId'];
        $record->email = $data['email'] ?? null;
        $record->emailVerified = $data['emailVerified'] ?? false;
        $record->displayName = $data['displayName'] ?? null;
        $record->photoUrl = $data['photoUrl'] ?? null;
        $record->phoneNumber = $data['phoneNumber'] ?? null;
        $record->disabled = $data['disabled'] ?? false;
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
        return \array_map(static function (array $userInfoData) {
            return UserInfo::fromResponseData($userInfoData);
        }, $data['providerUserInfo'] ?? []);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $data = \get_object_vars($this);

        $data['tokensValidAfterTime'] = $this->tokensValidAfterTime
            ? $this->tokensValidAfterTime->format(\DATE_ATOM)
            : null;

        return $data;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        if (\mb_strtolower($name) === 'customattributes') {
            Deprecation::trigger(__CLASS__.'::customAttributes', __CLASS__.'::customClaims');

            return $this->customClaims;
        }

        return $this->{$name};
    }
}

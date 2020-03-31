<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

class UserInfo implements \JsonSerializable
{
    /** @var string|null */
    public $uid;

    /** @var string|null */
    public $displayName;

    /** @var string|null */
    public $email;

    /** @var string|null */
    public $photoUrl;

    /** @var string|null */
    public $providerId;

    /** @var string|null */
    public $phoneNumber;

    /**
     * @param array<string, string> $data
     */
    public static function fromResponseData(array $data): self
    {
        $info = new self();
        $info->uid = $data['rawId'] ?? null;
        $info->displayName = $data['displayName'] ?? null;
        $info->email = $data['email'] ?? null;
        $info->photoUrl = $data['photoUrl'] ?? null;
        $info->providerId = $data['providerId'] ?? null;
        $info->phoneNumber = $data['phoneNumber'] ?? null;

        return $info;
    }

    /**
     * @return array<string, string|null>
     */
    public function jsonSerialize(): array
    {
        return \get_object_vars($this);
    }
}

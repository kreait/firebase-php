<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

class UserInfo implements \JsonSerializable
{
    public $uid;
    public $displayName;
    public $email;
    public $photoUrl;
    public $providerId;
    public $phoneNumber;

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

    public function jsonSerialize()
    {
        return \get_object_vars($this);
    }
}

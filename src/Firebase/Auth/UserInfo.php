<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use Kreait\Firebase\Exception\AuthException;

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
        if (!array_key_exists('providerId', $data) || !array_key_exists('rawId', $data)) {
            throw new AuthException('Invalid user info');
        }

        $info = new self();
        $info->uid = $data['rawId'];
        $info->displayName = $data['displayName'] ?? null;
        $info->email = $data['email'] ?? null;
        $info->photoUrl = $data['photoUrl'] ?? null;
        $info->providerId = $data['providerId'];
        $info->phoneNumber = $data['phoneNumber'] ?? null;

        return $info;
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}

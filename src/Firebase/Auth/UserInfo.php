<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

class UserInfo implements \JsonSerializable
{
    public ?string $uid = null;
    public ?string $displayName = null;
    public ?string $email = null;
    public ?string $photoUrl = null;
    public ?string $providerId = null;
    public ?string $phoneNumber = null;

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

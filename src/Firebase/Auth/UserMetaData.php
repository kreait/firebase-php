<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use DateTimeImmutable;
use JsonSerializable;
use Kreait\Firebase\Util\DT;

use const DATE_ATOM;

use function get_object_vars;

class UserMetaData implements JsonSerializable
{
    public ?DateTimeImmutable $createdAt = null;
    public ?DateTimeImmutable $lastLoginAt = null;
    public ?DateTimeImmutable $passwordUpdatedAt = null;

    /**
     * The time at which the user was last active (ID token refreshed), or null
     * if the user was never active.
     */
    public ?DateTimeImmutable $lastRefreshAt = null;

    /**
     * @param array<string, mixed> $data
     */
    public static function fromResponseData(array $data): self
    {
        $metadata = new self();
        $metadata->createdAt = DT::toUTCDateTimeImmutable($data['createdAt']);

        if ($data['lastLoginAt'] ?? null) {
            $metadata->lastLoginAt = DT::toUTCDateTimeImmutable($data['lastLoginAt']);
        }

        if ($data['passwordUpdatedAt'] ?? null) {
            $metadata->passwordUpdatedAt = DT::toUTCDateTimeImmutable($data['passwordUpdatedAt']);
        }

        if ($data['lastRefreshAt'] ?? null) {
            $metadata->lastRefreshAt = DT::toUTCDateTimeImmutable($data['lastRefreshAt']);
        }

        return $metadata;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $data = get_object_vars($this);

        $data['createdAt'] = $this->createdAt !== null ? $this->createdAt->format(DATE_ATOM) : null;
        $data['lastLoginAt'] = $this->lastLoginAt !== null ? $this->lastLoginAt->format(DATE_ATOM) : null;

        return $data;
    }
}

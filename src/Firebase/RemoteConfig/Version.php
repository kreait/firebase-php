<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use Kreait\Firebase\Util\DT;

final class Version
{
    /**
     * @var VersionNumber
     */
    private $versionNumber;

    /**
     * @var User
     */
    private $user;

    /**
     * @var \DateTimeImmutable
     */
    private $updatedAt;

    /**
     * @var string
     */
    private $description;

    /**
     * @var UpdateOrigin
     */
    private $updateOrigin;

    /**
     * @var UpdateType
     */
    private $updateType;

    /**
     * @var VersionNumber|null
     */
    private $rollbackSource;

    private function __construct()
    {
    }

    /**
     * @internal
     */
    public static function fromArray(array $data): self
    {
        $new = new self();
        $new->versionNumber = VersionNumber::fromValue($data['versionNumber']);
        $new->user = User::fromArray($data['updateUser']);
        $new->updatedAt = DT::toUTCDateTimeImmutable($data['updateTime']);
        $new->description = $data['description'] ?? '';

        $new->updateOrigin = ($data['updateOrigin'] ?? null)
            ? UpdateOrigin::fromValue($data['updateOrigin'])
            : UpdateOrigin::fromValue(UpdateOrigin::UNSPECIFIED);

        $new->updateType = ($data['updateType'] ?? null)
            ? UpdateType::fromValue($data['updateType'])
            : UpdateType::fromValue(UpdateType::UNSPECIFIED);

        $new->rollbackSource = ($data['rollbackSource'] ?? null)
            ? VersionNumber::fromValue($data['rollbackSource'])
            : null;

        return $new;
    }

    public function versionNumber(): VersionNumber
    {
        return $this->versionNumber;
    }

    public function user(): User
    {
        return $this->user;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function updateOrigin(): UpdateOrigin
    {
        return $this->updateOrigin;
    }

    public function updateType(): UpdateType
    {
        return $this->updateType;
    }

    /**
     * @return VersionNumber|null
     */
    public function rollbackSource()
    {
        return $this->rollbackSource;
    }
}

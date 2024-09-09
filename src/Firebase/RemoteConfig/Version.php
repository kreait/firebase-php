<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use DateTimeImmutable;
use Kreait\Firebase\Util\DT;

use function array_key_exists;

/**
 * @phpstan-import-type RemoteConfigUserShape from User
 *
 * @phpstan-type RemoteConfigVersionShape array{
 *    versionNumber: non-empty-string,
 *    updateTime: non-empty-string,
 *    updateUser: RemoteConfigUserShape,
 *    description?: string|null,
 *    updateOrigin: non-empty-string,
 *    updateType: non-empty-string,
 *    rollbackSource?: non-empty-string
 * }
 */
final class Version
{
    private function __construct(
        private readonly VersionNumber $versionNumber,
        private readonly User $user,
        private readonly string $description,
        private readonly DateTimeImmutable $updatedAt,
        private readonly UpdateOrigin $updateOrigin,
        private readonly UpdateType $updateType,
        private readonly ?VersionNumber $rollbackSource,
    ) {
    }

    /**
     * @internal
     *
     * @param RemoteConfigVersionShape $data
     */
    public static function fromArray(array $data): self
    {
        $versionNumber = VersionNumber::fromValue($data['versionNumber']);
        $user = User::fromArray($data['updateUser']);
        $updatedAt = DT::toUTCDateTimeImmutable($data['updateTime']);
        $description = $data['description'] ?? '';
        $updateOrigin = UpdateOrigin::fromValue($data['updateOrigin']);
        $updateType = UpdateType::fromValue($data['updateType']);

        $rollbackSource = array_key_exists('rollbackSource', $data)
            ? VersionNumber::fromValue($data['rollbackSource'])
            : null;

        return new self(
            $versionNumber,
            $user,
            $description,
            $updatedAt,
            $updateOrigin,
            $updateType,
            $rollbackSource,
        );
    }

    public function versionNumber(): VersionNumber
    {
        return $this->versionNumber;
    }

    public function user(): User
    {
        return $this->user;
    }

    public function updatedAt(): DateTimeImmutable
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

    public function rollbackSource(): ?VersionNumber
    {
        return $this->rollbackSource;
    }
}

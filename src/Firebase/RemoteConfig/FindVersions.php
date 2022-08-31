<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use DateTimeImmutable;
use DateTimeInterface;
use Kreait\Firebase\Util\DT;

class FindVersions
{
    private ?DateTimeImmutable $since = null;
    private ?DateTimeImmutable $until = null;
    private ?VersionNumber $upToVersion = null;
    private ?int $limit = null;
    private ?int $pageSize = null;

    private function __construct()
    {
    }

    public static function all(): self
    {
        return new self();
    }

    /**
     * @param array<string, mixed> $params
     */
    public static function fromArray(array $params): self
    {
        $query = self::all();

        if ($value = $params['startingAt'] ?? $params['startTime'] ?? $params['since'] ?? null) {
            $query = $query->startingAt(DT::toUTCDateTimeImmutable($value));
        }

        if ($value = $params['endingAt'] ?? $params['endTime'] ?? $params['until'] ?? null) {
            $query = $query->endingAt(DT::toUTCDateTimeImmutable($value));
        }

        if ($value = $params['lastVersionBeing'] ?? $params['endVersionNumber'] ?? $params['up_to_version'] ?? null) {
            $versionNumber = $value instanceof VersionNumber ? $value : VersionNumber::fromValue($value);
            $query = $query->upToVersion($versionNumber);
        }

        if ($value = $params['pageSize'] ?? $params['page_size'] ?? null) {
            $query = $query->withPageSize((int) $value);
        }

        if ($value = $params['limit'] ?? null) {
            return $query->withLimit((int) $value);
        }

        return $query;
    }

    public function startingAt(DateTimeInterface $startTime): self
    {
        $query = clone $this;
        $query->since = DT::toUTCDateTimeImmutable($startTime);

        return $query;
    }

    public function since(): ?DateTimeImmutable
    {
        return $this->since;
    }

    public function endingAt(DateTimeInterface $endTime): self
    {
        $query = clone $this;
        $query->until = DT::toUTCDateTimeImmutable($endTime);

        return $query;
    }

    public function until(): ?DateTimeImmutable
    {
        return $this->until;
    }

    public function upToVersion(VersionNumber $versionNumber): self
    {
        $query = clone $this;
        $query->upToVersion = $versionNumber;

        return $query;
    }

    public function lastVersionNumber(): ?VersionNumber
    {
        return $this->upToVersion;
    }

    public function withPageSize(int $pageSize): self
    {
        $query = clone $this;
        $query->pageSize = $pageSize;

        return $query;
    }

    public function pageSize(): ?int
    {
        return $this->pageSize;
    }

    public function withLimit(int $limit): self
    {
        $query = clone $this;
        $query->limit = $limit;

        return $query;
    }

    public function limit(): ?int
    {
        return $this->limit;
    }
}

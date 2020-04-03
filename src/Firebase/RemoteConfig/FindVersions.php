<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use DateTimeInterface;
use Kreait\Firebase\Util\DT;

class FindVersions
{
    /** @var \DateTimeImmutable|null */
    private $since;

    /** @var \DateTimeImmutable|null */
    private $until;

    /** @var VersionNumber|null */
    private $upToVersion;

    /** @var int|null */
    private $limit;

    /** @var int|null */
    private $pageSize;

    private function __construct()
    {
    }

    public static function all(): self
    {
        return new self();
    }

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
            $query = $query->withLimit((int) $value);
        }

        return $query;
    }

    public function startingAt(DateTimeInterface $startTime): self
    {
        $query = clone $this;
        $query->since = DT::toUTCDateTimeImmutable($startTime);

        return $query;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function since()
    {
        return $this->since;
    }

    public function endingAt(DateTimeInterface $endTime): self
    {
        $query = clone $this;
        $query->until = DT::toUTCDateTimeImmutable($endTime);

        return $query;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function until()
    {
        return $this->until;
    }

    public function upToVersion(VersionNumber $versionNumber): self
    {
        $query = clone $this;
        $query->upToVersion = $versionNumber;

        return $query;
    }

    /**
     * @return VersionNumber|null
     */
    public function lastVersionNumber()
    {
        return $this->upToVersion;
    }

    public function withPageSize(int $pageSize): self
    {
        $query = clone $this;
        $query->pageSize = $pageSize;

        return $query;
    }

    /**
     * @return int|null
     */
    public function pageSize()
    {
        return $this->pageSize;
    }

    public function withLimit(int $limit): self
    {
        $query = clone $this;
        $query->limit = $limit;

        return $query;
    }

    /**
     * @return int|null
     */
    public function limit()
    {
        return $this->limit;
    }
}

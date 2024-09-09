<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use DateTimeImmutable;
use DateTimeInterface;
use Kreait\Firebase\Util\DT;

/**
 * @phpstan-type FindVersionsShape array{
 *     startingAt?: non-empty-string,
 *     startTime?: non-empty-string,
 *     since?: non-empty-string,
 *     endingAt?: non-empty-string,
 *     endTime?: non-empty-string,
 *     until?: non-empty-string,
 *     lastVersionBeing?: VersionNumber|positive-int|non-empty-string,
 *     endVersionNumber?: VersionNumber|positive-int|non-empty-string,
 *     up_to_version?: VersionNumber|positive-int|non-empty-string,
 *     pageSize?: positive-int|non-empty-string,
 *     page_size?: positive-int|non-empty-string,
 *     limit?: positive-int|non-empty-string
 * }
 */
class FindVersions
{
    private ?DateTimeImmutable $since = null;
    private ?DateTimeImmutable $until = null;
    private ?VersionNumber $upToVersion = null;

    /**
     * @var positive-int|null
     */
    private ?int $limit = null;

    /**
     * @var positive-int|null
     */
    private ?int $pageSize = null;

    private function __construct()
    {
    }

    public static function all(): self
    {
        return new self();
    }

    /**
     * @param FindVersionsShape $params
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
            $value = (int) $value;

            if ($value >= 1) {
                // We can't throw an exception here, although we shouldn't because of backward compatibility
                $query = $query->withPageSize($value);
            }
        }

        if ($value = $params['limit'] ?? null) {
            $value = (int) $value;

            if ($value >= 1) {
                // We can't throw an exception here, although we shouldn't because of backward compatibility
                $query = $query->withLimit($value);
            }
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

    /**
     * @param positive-int $pageSize
     */
    public function withPageSize(int $pageSize): self
    {
        $query = clone $this;
        $query->pageSize = $pageSize;

        return $query;
    }

    /**
     * @return positive-int|null $pageSize
     */
    public function pageSize(): ?int
    {
        return $this->pageSize;
    }

    /**
     * @param positive-int $limit
     */
    public function withLimit(int $limit): self
    {
        $query = clone $this;
        $query->limit = $limit;

        return $query;
    }

    /**
     * @return positive-int|null
     */
    public function limit(): ?int
    {
        return $this->limit;
    }
}

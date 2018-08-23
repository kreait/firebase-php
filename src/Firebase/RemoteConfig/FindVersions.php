<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use Kreait\Firebase\Util\DT;

class FindVersions
{
    /**
     * @var \DateTimeImmutable|null
     */
    private $since;

    /**
     * @var \DateTimeImmutable|null
     */
    private $until;

    /**
     * @var VersionNumber|null
     */
    private $upToVersion;

    /**
     * @var int|null
     */
    private $limit;

    public static function fromArray(array $params): self
    {
        $new = new self();

        if ($value = $params['since'] ?? null) {
            $new->since = DT::toUTCDateTimeImmutable($value);
        }

        if ($value = $params['until'] ?? null) {
            $new->until = DT::toUTCDateTimeImmutable($value);
        }

        if ($value = $params['up_to_version'] ?? null) {
            $new->upToVersion = $value instanceof VersionNumber ? $value : VersionNumber::fromValue($value);
        }

        if ($value = $params['limit'] ?? null) {
            $new->limit = (int) $value;
        }

        return $new;
    }

    public static function all(): self
    {
        return new self();
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function since()
    {
        return $this->since;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function until()
    {
        return $this->until;
    }

    /**
     * @return VersionNumber|null
     */
    public function upToVersion()
    {
        return $this->upToVersion;
    }

    /**
     * @return int|null
     */
    public function limit()
    {
        return $this->limit;
    }
}

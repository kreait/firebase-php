<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use Kreait\Firebase\Value;

final class UpdateType implements \JsonSerializable, Value
{
    const UNSPECIFIED = 'REMOTE_CONFIG_UPDATE_TYPE_UNSPECIFIED';
    const INCREMENTAL_UPDATE = 'INCREMENTAL_UPDATE';
    const FORCED_UPDATE = 'FORCED_UPDATE';
    const ROLLBACK = 'ROLLBACK';

    /**
     * @var string
     */
    private $value;

    private function __construct()
    {
    }

    public static function fromValue($value): self
    {
        $new = new self();
        $new->value = (string) $value;

        return $new;
    }

    public function __toString()
    {
        return $this->value;
    }

    public function jsonSerialize()
    {
        return $this->value;
    }

    public function equalsTo($other): bool
    {
        return $this->value === (string) $other;
    }
}

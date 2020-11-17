<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

final class TenantId
{
    /** @var string */
    private $value;

    private function __construct()
    {
    }

    public static function fromString(string $value): self
    {
        $id = new self();
        $id->value = $value;

        return $id;
    }

    public function toString(): string
    {
        return $this->value;
    }
}

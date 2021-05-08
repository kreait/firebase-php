<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

interface ActionCodeSettings
{
    /**
     * @return array<string, bool|string>
     */
    public function toArray(): array;
}

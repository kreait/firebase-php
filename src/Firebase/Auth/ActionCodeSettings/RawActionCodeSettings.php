<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth\ActionCodeSettings;

use Kreait\Firebase\Auth\ActionCodeSettings;

final class RawActionCodeSettings implements ActionCodeSettings
{
    /** @var array<bool|string> */
    private array $settings;

    /**
     * @param array<string, bool|string> $settings
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return array<string, bool|string>
     */
    public function toArray(): array
    {
        return $this->settings;
    }
}

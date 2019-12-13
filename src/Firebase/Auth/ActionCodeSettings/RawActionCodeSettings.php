<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth\ActionCodeSettings;

use Kreait\Firebase\Auth\ActionCodeSettings;

final class RawActionCodeSettings implements ActionCodeSettings
{
    /** @var array */
    private $settings;

    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    public function toArray(): array
    {
        return $this->settings;
    }
}

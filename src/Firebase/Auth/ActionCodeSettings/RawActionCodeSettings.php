<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth\ActionCodeSettings;

use Kreait\Firebase\Auth\ActionCodeSettings;

final class RawActionCodeSettings implements ActionCodeSettings
{
    /** @var array<mixed> */
    private $settings;

    /**
     * @param array<mixed> $settings
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return array<mixed>
     */
    public function toArray(): array
    {
        return $this->settings;
    }
}

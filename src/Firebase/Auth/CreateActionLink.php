<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use Kreait\Firebase\Auth\ActionCodeSettings\ValidatedActionCodeSettings;
use Kreait\Firebase\Value\Email;

final class CreateActionLink
{
    /** @var string */
    private $type;

    /** @var Email */
    private $email;

    /** @var ActionCodeSettings|null */
    private $settings;

    /** @var string|null */
    private $tenantId;

    private function __construct()
    {
    }

    public static function new(string $type, Email $email, ActionCodeSettings $settings, ?string $tenantId = null): self
    {
        $instance = new self();
        $instance->type = $type;
        $instance->email = $email;
        $instance->settings = $settings;
        $instance->tenantId = $tenantId;

        return $instance;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function settings(): ActionCodeSettings
    {
        return $this->settings ?? ValidatedActionCodeSettings::empty();
    }

    public function tenantId(): ?string
    {
        return $this->tenantId;
    }
}

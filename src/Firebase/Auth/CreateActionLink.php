<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use Kreait\Firebase\Auth\ActionCodeSettings\ValidatedActionCodeSettings;
use Kreait\Firebase\Value\Email;

final class CreateActionLink
{
    private string $type;
    private Email $email;
    private ActionCodeSettings $settings;
    private ?string $tenantId = null;
    private ?string $locale = null;

    private function __construct(string $type, Email $email, ActionCodeSettings $settings)
    {
        $this->type = $type;
        $this->email = $email;
        $this->settings = $settings;
    }

    public static function new(string $type, Email $email, ActionCodeSettings $settings, ?string $tenantId = null, ?string $locale = null): self
    {
        $instance = new self($type, $email, $settings);
        $instance->tenantId = $tenantId;
        $instance->locale = $locale;

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

    public function locale(): ?string
    {
        return $this->locale;
    }
}

<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use Kreait\Firebase\Auth\ActionCodeSettings\ValidatedActionCodeSettings;
use Kreait\Firebase\Value\Email;
use Stringable;

final class CreateActionLink
{
    private string $type;
    private string $email;
    private ActionCodeSettings $settings;
    private ?string $tenantId = null;
    private ?string $locale = null;

    private function __construct(string $type, string $email, ActionCodeSettings $settings)
    {
        $this->type = $type;
        $this->email = $email;
        $this->settings = $settings;
    }

    /**
     * @param Stringable|string $email
     */
    public static function new(string $type, $email, ActionCodeSettings $settings, ?string $tenantId = null, ?string $locale = null): self
    {
        $email = (string) (new Email((string) $email));

        $instance = new self($type, $email, $settings);
        $instance->tenantId = $tenantId;
        $instance->locale = $locale;

        return $instance;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function email(): string
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

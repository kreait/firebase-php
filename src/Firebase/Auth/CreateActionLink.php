<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use Kreait\Firebase\Auth\ActionCodeSettings\ValidatedActionCodeSettings;
use Kreait\Firebase\Value\Email;
use Stringable;

/**
 * @internal
 */
final class CreateActionLink
{
    private function __construct(
        private readonly ?string $tenantId,
        private readonly ?string $locale,
        private readonly string $type,
        private readonly string $email,
        private readonly ActionCodeSettings $settings,
    ) {
    }

    public static function new(string $type, Stringable|string $email, ActionCodeSettings $settings, ?string $tenantId = null, ?string $locale = null): self
    {
        $email = Email::fromString((string) $email)->value;

        return new self($tenantId, $locale, $type, $email, $settings);
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

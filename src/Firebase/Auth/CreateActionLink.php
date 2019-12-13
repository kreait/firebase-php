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

    /** @var ActionCodeSettings */
    private $settings;

    private function __construct()
    {
    }

    public static function new(string $type, Email $email, ActionCodeSettings $settings): self
    {
        $instance = new self();
        $instance->type = $type;
        $instance->email = $email;
        $instance->settings = $settings;

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
}

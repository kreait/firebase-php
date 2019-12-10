<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

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

    public static function forEmailSignIn(Email $email, ActionCodeSettings $settings): self
    {
        return self::new('EMAIL_SIGNIN', $email, $settings);
    }

    public static function forPasswordReset(Email $email, ActionCodeSettings $settings): self
    {
        return self::new('PASSWORD_RESET', $email, $settings);
    }

    public static function forEmailVerification(Email $email, ActionCodeSettings $settings): self
    {
        return self::new('VERIFY_EMAIL', $email, $settings);
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
        return $this->settings ?? ActionCodeSettings::none();
    }
}

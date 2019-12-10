<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

final class ActionCodeSettings
{
    /** @var array */
    private $settings = [];

    private function __construct()
    {
    }

    public static function none(): self
    {
        return new self();
    }

    public static function validated(array $settings): self
    {
        // TODO Validate the settings :)
        return self::unvalidated($settings);
    }

    public static function unvalidated(array $settings): self
    {
        $instance = new self();
        $instance->settings = $settings;

        return $instance;
    }

    public function toArray(): array
    {
        return \array_filter($this->settings, static function ($value) {
            return $value !== null;
        });
    }
}

<?php

declare(strict_types=1);

namespace Kreait\Firebase\Value;

class Provider implements \JsonSerializable
{
    public const ANONYMOUS = 'anonymous';
    public const CUSTOM = 'custom';
    public const FACEBOOK = 'facebook.com';
    public const FIREBASE = 'firebase';
    public const GITHUB = 'github.com';
    public const GOOGLE = 'google.com';
    public const PASSWORD = 'password';
    public const PHONE = 'phone';
    public const TWITTER = 'twitter.com';
    public const APPLE = 'apple.com';

    private string $value;

    /**
     * @internal
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }

    /**
     * @param self|string $other
     */
    public function equalsTo($other): bool
    {
        return $this->value === (string) $other;
    }
}

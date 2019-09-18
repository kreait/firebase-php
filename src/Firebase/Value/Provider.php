<?php

declare(strict_types=1);

namespace Kreait\Firebase\Value;

use Kreait\Firebase\Value;

class Provider implements \JsonSerializable, Value
{
    const ANONYMOUS = 'anonymous';
    const CUSTOM = 'custom';
    const FACEBOOK = 'facebook.com';
    const FIREBASE = 'firebase';
    const GITHUB = 'github.com';
    const GOOGLE = 'google.com';
    const PASSWORD = 'password';
    const PHONE = 'phone';
    const TWITTER = 'twitter.com';

    /**
     * @var string
     */
    private $value;

    /**
     * @internal
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function __toString()
    {
        return $this->value;
    }

    public function jsonSerialize()
    {
        return $this->value;
    }

    public function equalsTo($other): bool
    {
        return $this->value === (string) $other;
    }
}

<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use Kreait\Firebase\Exception\InvalidArgumentException;

class TagColor
{
    const BLUE = 'BLUE';
    const BROWN = 'BROWN';
    const CYAN = 'CYAN';
    const DEEP_ORANGE = 'DEEP_ORANGE';
    const GREEN = 'GREEN';
    const INDIGO = 'INDIGO';
    const LIME = 'LIME';
    const ORANGE = 'ORANGE';
    const PINK = 'PINK';
    const PURPLE = 'PURPLE';
    const TEAL = 'TEAL';

    const VALID_COLORS = [
        self::BLUE, self::BROWN, self::CYAN, self::DEEP_ORANGE, self::GREEN, self::INDIGO, self::LIME,
        self::ORANGE, self::PINK, self::PURPLE, self::TEAL,
    ];

    /**
     * @var string
     */
    private $value;

    public function __construct(string $value)
    {
        $value = \mb_strtoupper($value);

        if (!\in_array($value, self::VALID_COLORS, true)) {
            throw new InvalidArgumentException(
                \sprintf(
                    'Invalid tag color "%s". Supported colors are "%s".',
                    $value, \implode('", "', self::VALID_COLORS)
            ));
        }

        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString()
    {
        return $this->value;
    }
}

<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use Kreait\Firebase\Exception\InvalidArgumentException;

use function implode;
use function in_array;
use function mb_strtoupper;
use function sprintf;

class TagColor
{
    public const BLUE = 'BLUE';
    public const BROWN = 'BROWN';
    public const CYAN = 'CYAN';
    public const DEEP_ORANGE = 'DEEP_ORANGE';
    public const GREEN = 'GREEN';
    public const INDIGO = 'INDIGO';
    public const LIME = 'LIME';
    public const ORANGE = 'ORANGE';
    public const PINK = 'PINK';
    public const PURPLE = 'PURPLE';
    public const TEAL = 'TEAL';
    public const VALID_COLORS = [
        self::BLUE, self::BROWN, self::CYAN, self::DEEP_ORANGE, self::GREEN, self::INDIGO, self::LIME,
        self::ORANGE, self::PINK, self::PURPLE, self::TEAL,
    ];

    /**
     * @var non-empty-string
     */
    private string $value;

    /**
     * @param non-empty-string $value
     */
    public function __construct(string $value)
    {
        $value = mb_strtoupper($value);

        if (!in_array($value, self::VALID_COLORS, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid tag color "%s". Supported colors are "%s".',
                    $value,
                    implode('", "', self::VALID_COLORS),
                ),
            );
        }

        $this->value = $value;
    }

    /**
     * @return non-empty-string
     */
    public function __toString()
    {
        return $this->value;
    }

    /**
     * @return non-empty-string
     */
    public function value(): string
    {
        return $this->value;
    }
}

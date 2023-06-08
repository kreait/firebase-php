<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Stringable;

use function implode;
use function in_array;
use function mb_strtoupper;
use function sprintf;

class TagColor implements Stringable
{
    final public const BLUE = 'BLUE';
    final public const BROWN = 'BROWN';
    final public const CYAN = 'CYAN';
    final public const DEEP_ORANGE = 'DEEP_ORANGE';
    final public const GREEN = 'GREEN';
    final public const INDIGO = 'INDIGO';
    final public const LIME = 'LIME';
    final public const ORANGE = 'ORANGE';
    final public const PINK = 'PINK';
    final public const PURPLE = 'PURPLE';
    final public const TEAL = 'TEAL';
    final public const VALID_COLORS = [
        self::BLUE, self::BROWN, self::CYAN, self::DEEP_ORANGE, self::GREEN, self::INDIGO, self::LIME,
        self::ORANGE, self::PINK, self::PURPLE, self::TEAL,
    ];

    /**
     * @var non-empty-string
     */
    private readonly string $value;

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

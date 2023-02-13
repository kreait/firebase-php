<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Value;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Value\Uid;
use PHPUnit\Framework\TestCase;

use function str_repeat;

/**
 * @internal
 */
final class UidTest extends TestCase
{
    /**
     * @dataProvider validValues
     */
    public function testWithValidValue(string $uid): void
    {
        $this->assertSame($uid, Uid::fromString($uid)->value);
    }

    /**
     * @dataProvider invalidValues
     */
    public function testWithInvalidValue(string $uid): void
    {
        $this->expectException(InvalidArgumentException::class);
        Uid::fromString($uid);
    }

    /**
     * @return array<string, array<string>>
     */
    public static function validValues(): array
    {
        return [
            'uid' => ['uid'],
        ];
    }

    /**
     * @return array<string, array<string>>
     */
    public static function invalidValues(): array
    {
        return [
            'empty string' => [''],
            'too long' => [str_repeat('x', 129)],
        ];
    }
}

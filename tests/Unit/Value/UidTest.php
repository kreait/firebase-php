<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Value;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Value\Uid;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class UidTest extends TestCase
{
    /**
     * @dataProvider validValues
     *
     * @param mixed $value
     */
    public function testWithValidValue($value): void
    {
        $uid = new Uid($value);

        $this->assertSame($value, (string) $uid);
        $this->assertSame($value, $uid->jsonSerialize());
        $this->assertTrue($uid->equalsTo($value));
    }

    /**
     * @dataProvider invalidValues
     *
     * @param mixed $value
     */
    public function testWithInvalidValue($value): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Uid($value);
    }

    /**
     * @return array<string, array<string>>
     */
    public function validValues(): array
    {
        return [
            'uid' => ['uid'],
        ];
    }

    /**
     * @return array<string, array<string>>
     */
    public function invalidValues(): array
    {
        return [
            'empty string' => [''],
            'too long' => [\str_repeat('x', 129)],
        ];
    }
}

<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Value;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Value\Url;
use PHPUnit\Framework\TestCase;
use Stringable;

/**
 * @internal
 */
final class UrlTest extends TestCase
{
    /**
     * @dataProvider validValues
     *
     * @param Stringable|string $value
     */
    public function testWithValidValue($value): void
    {
        $url = Url::fromString($value)->value;

        $check = (string) $value;

        $this->assertSame($check, $url);
    }

    /**
     * @dataProvider invalidValues
     */
    public function testWithInvalidValue(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        Url::fromString($value);
    }

    /**
     * @return array<non-empty-string, array<string>>
     */
    public function validValues(): array
    {
        return [
            'string' => ['https://domain.tld'],
        ];
    }

    /**
     * @return array<string, array<string>>
     */
    public function invalidValues(): array
    {
        return [
            'https:///domain.tld' => ['https:///domain.tld'],
            'http://:80' => ['http://:80'],
        ];
    }
}

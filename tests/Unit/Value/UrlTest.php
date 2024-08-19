<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Value;

use Iterator;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Value\Url;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Stringable;

/**
 * @internal
 */
final class UrlTest extends TestCase
{
    #[DataProvider('validValues')]
    #[Test]
    public function withValidValue(Stringable|string $value): void
    {
        $url = Url::fromString($value)->value;

        $check = (string) $value;

        $this->assertSame($check, $url);
    }

    #[DataProvider('invalidValues')]
    #[Test]
    public function withInvalidValue(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        Url::fromString($value);
    }

    public static function validValues(): Iterator
    {
        yield 'string' => ['https://example.com'];
    }

    public static function invalidValues(): Iterator
    {
        yield 'https:///example.com' => ['https:///example.com'];
        yield 'http://:80' => ['http://:80'];
        yield '(empty)' => [''];
    }
}

<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Kreait\Firebase\Exception\Messaging\InvalidArgument;
use Kreait\Firebase\Messaging\Topic;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class TopicTest extends TestCase
{
    #[DataProvider('valueProvider')]
    #[Test]
    public function fromValue(string $expected, string $value): void
    {
        $this->assertSame($expected, Topic::fromValue($value)->value());
    }

    #[DataProvider('invalidValueProvider')]
    #[Test]
    public function fromInvalidValue(string $value): void
    {
        $this->expectException(InvalidArgument::class);
        Topic::fromValue($value);
    }

    public static function valueProvider(): \Iterator
    {
        yield 'no slashes' => ['foo', 'foo'];
        yield 'leading slash' => ['foo', '/foo'];
        yield 'trailing slash' => ['foo', 'foo/'];
        yield 'with topic prefix' => ['foo', '/topic/foo'];
    }

    public static function invalidValueProvider(): \Iterator
    {
        yield '$' => ['$'];
        yield 'ä' => ['ä'];
        yield 'é' => ['é'];
        yield '(empty)' => [''];
    }
}

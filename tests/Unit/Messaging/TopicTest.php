<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Kreait\Firebase\Exception\Messaging\InvalidArgument;
use Kreait\Firebase\Messaging\Topic;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class TopicTest extends TestCase
{
    /**
     * @dataProvider valueProvider
     *
     * @test
     */
    public function fromValue(string $expected, string $value): void
    {
        $this->assertSame($expected, Topic::fromValue($value)->value());
    }

    /**
     * @dataProvider invalidValueProvider
     *
     * @test
     */
    public function fromInvalidValue(string $value): void
    {
        $this->expectException(InvalidArgument::class);
        Topic::fromValue($value);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function valueProvider(): array
    {
        return [
            'no slashes' => ['foo', 'foo'],
            'leading slash' => ['foo', '/foo'],
            'trailing slash' => ['foo', 'foo/'],
            'with topic prefix' => ['foo', '/topic/foo'],
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function invalidValueProvider(): array
    {
        return [
            '$' => ['$'],
            'ä' => ['ä'],
            'é' => ['é'],
            '(empty)' => [''],
        ];
    }
}

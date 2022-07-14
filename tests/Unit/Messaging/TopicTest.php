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
     */
    public function testFromValue(string $expected, string $value): void
    {
        $this->assertSame($expected, Topic::fromValue($value)->value());
    }

    /**
     * @dataProvider invalidValueProvider
     */
    public function testFromInvalidValue(string $value): void
    {
        $this->expectException(InvalidArgument::class);
        Topic::fromValue($value);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function valueProvider(): array
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
    public function invalidValueProvider(): array
    {
        return [
            '$' => ['$'],
            'ä' => ['ä'],
            'é' => ['é'],
        ];
    }
}

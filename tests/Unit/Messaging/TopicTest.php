<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Kreait\Firebase\Exception\Messaging\InvalidArgument;
use Kreait\Firebase\Messaging\Topic;
use PHPUnit\Framework\TestCase;

class TopicTest extends TestCase
{
    /**
     * @dataProvider valueProvider
     */
    public function testFromValue($expected, $value)
    {
        $this->assertSame($expected, Topic::fromValue($value)->value());
    }

    /**
     * @dataProvider invalidValueProvider
     */
    public function testFromInvalidValue($value)
    {
        $this->expectException(InvalidArgument::class);
        Topic::fromValue($value);
    }

    public function valueProvider()
    {
        return [
            ['foo', 'foo'],
            ['foo', '/foo'],
            ['foo', 'foo/'],
            ['foo', '/topic/foo'],
        ];
    }

    public function invalidValueProvider()
    {
        return [
            ['$'],
            ['ä'],
            ['é'],
        ];
    }
}

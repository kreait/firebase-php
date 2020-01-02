<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use InvalidArgumentException;
use Kreait\Firebase\Messaging\MessageData;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class MessageDataTest extends TestCase
{
    /**
     * @test
     */
    public function it_accepts_stringable_object_values()
    {
        $stringable = new class() {
            public function __toString()
            {
                return 'value';
            }
        };

        $data = MessageData::fromArray(['key' => $stringable]);
        $this->assertEquals(['key' => 'value'], $data->jsonSerialize());
    }

    /**
     * @test
     * @dataProvider invalidValues
     */
    public function it_rejects_invalid_values(array $data)
    {
        $this->expectException(InvalidArgumentException::class);
        MessageData::fromArray($data);
    }

    public function invalidValues()
    {
        return [
            'integer key' => [
                [0 => 'string'],
            ],
            'integer value' => [
                ['key' => 0],
            ],
            'boolean value' => [
                ['key' => true],
            ],
        ];
    }
}

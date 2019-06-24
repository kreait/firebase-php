<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Messaging\ConditionalMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
class ConditionalMessageTest extends UnitTestCase
{
    /**
     * @dataProvider validDataProvider
     */
    public function testCreateFromArray(array $input)
    {
        $message = ConditionalMessage::fromArray($input);

        $this->assertSame($input['condition'], $message->condition());

        if (isset($input['data'])) {
            $this->assertEquals($input['data'], $message->data());
        }

        if (isset($input['notification'])) {
            $this->assertInstanceOf(Notification::class, $message->notification());
        }
    }

    /**
     * @dataProvider invalidDataProvider
     */
    public function testCreateFromArrayWithInvalidData(array $data)
    {
        $this->expectException(InvalidArgumentException::class);

        ConditionalMessage::fromArray($data);
    }

    public function validDataProvider(): array
    {
        $base = ['condition' => "'foo' in topics || 'cats' in topics"];

        return [
            'with_data' => [$base + [
                'data' => ['foo' => 'bar'],
            ]],
            'with_notification' => [$base + [
                'notification' => [
                    'title' => 'notification title',
                    'body' => 'notification body',
                ],
            ]],
            'with_notification_and_data' => [$base + [
                'data' => ['foo' => 'bar'],
                'notification' => [
                    'title' => 'notification title',
                    'body' => 'notification body',
                ], ],
            ],
        ];
    }

    public function invalidDataProvider(): array
    {
        return [
            'missing_condition' => [[]],
            'invalid_notification' => [[
                'notification' => [],
            ]],
        ];
    }
}

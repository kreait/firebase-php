<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Messaging\MessageToTopic;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Tests\UnitTestCase;

class MessageToTopicTest extends UnitTestCase
{
    /**
     * @param array $input
     * @dataProvider validDataProvider
     */
    public function testCreateFromArray(array $input)
    {
        $message = MessageToTopic::fromArray($input);

        $this->assertSame($input['topic'], $message->topic());

        if (isset($input['data'])) {
            $this->assertEquals($input['data'], $message->data());
        }

        if (isset($input['notification'])) {
            $this->assertInstanceOf(Notification::class, $message->notification());
        }
    }

    /**
     * @param array $data
     *
     * @dataProvider invalidDataProvider
     */
    public function testCreateFromArrayWithInvalidData(array $data)
    {
        $this->expectException(InvalidArgumentException::class);

        MessageToTopic::fromArray($data);
    }

    public function validDataProvider(): array
    {
        $base = ['topic' => 'my-topic'];

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
            'missing_topic' => [[]],
            'invalid_notification' => [[
                'notification' => [],
            ]],
        ];
    }
}

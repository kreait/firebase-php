<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Messaging\MessageToRegistrationToken;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Tests\UnitTestCase;

class MessageToRegistrationTokenTest extends UnitTestCase
{
    /**
     * @param array $input
     * @dataProvider validDataProvider
     */
    public function testCreateFromArray(array $input)
    {
        $message = MessageToRegistrationToken::fromArray($input);

        $this->assertSame($input['token'], $message->token());

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

        MessageToRegistrationToken::fromArray($data);
    }

    public function validDataProvider(): array
    {
        $base = ['token' => 'my-token'];

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
            'missing_token' => [[]],
            'invalid_notification' => [[
                'notification' => [],
            ]],
        ];
    }
}

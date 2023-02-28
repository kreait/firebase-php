<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging\Processor;

use Beste\Json;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Processor\SetApnsContentAvailableIfNeeded;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class SetApnsContentAvailableIfNeededTest extends TestCase
{
    private SetApnsContentAvailableIfNeeded $processor;

    protected function setUp(): void
    {
        $this->processor = new SetApnsContentAvailableIfNeeded();
    }

    /**
     * @dataProvider provideMessagesWithExpectedContentAvailable
     *
     * @param array<mixed> $messageData
     *
     * @see https://github.com/kreait/firebase-php/pull/762
     */
    public function testItSetsTheExpectedPushType(bool $expected, array $messageData): void
    {
        $message = CloudMessage::fromArray($messageData);

        $processed = Json::decode(Json::encode(($this->processor)($message)), true);

        if ($expected === true) {
            $this->assertTrue(isset($processed['apns']['payload']['aps']['content-available']));
            $this->assertSame(1, $processed['apns']['payload']['aps']['content-available']);
        } else {
            $this->assertFalse(isset($processed['apns']['payload']['aps']['content-available']));
        }
    }

    /**
     * @return iterable<string, array{0: bool, 1: array<mixed>}>
     */
    public static function provideMessagesWithExpectedContentAvailable(): iterable
    {
        yield 'message data at root level -> true' => [
            true,
            [
                'data' => [
                    'key' => 'value',
                ],
            ],
        ];

        yield 'message data at apns level -> true' => [
            true,
            [
                'apns' => [
                    'payload' => [
                        'data' => [
                            'key' => 'value',
                        ],
                    ],
                ],
            ],
        ];

        yield 'both message and apns data -> true' => [
            true,
            [
                'data' => [
                    'key' => 'value',
                ],
                'apns' => [
                    'payload' => [
                        'data' => [
                            'key' => 'value',
                        ],
                    ],
                ],
            ],
        ];

        yield 'no data -> false' => [
            false,
            [
                'data' => [],
                'apns' => [
                    'payload' => [
                    ],
                ],
            ],
        ];
    }
}

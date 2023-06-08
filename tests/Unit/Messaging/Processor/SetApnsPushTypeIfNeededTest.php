<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging\Processor;

use Beste\Json;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Message;
use Kreait\Firebase\Messaging\Processor\SetApnsPushTypeIfNeeded;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class SetApnsPushTypeIfNeededTest extends TestCase
{
    private SetApnsPushTypeIfNeeded $processor;

    protected function setUp(): void
    {
        $this->processor = new SetApnsPushTypeIfNeeded();
    }

    /**
     * @param non-empty-string|null $expected
     * @param array<mixed> $messageData
     */
    #[DataProvider('provideMessagesWithExpectedPushType')]
    #[Test]
    public function itSetsTheExpectedPushType(?string $expected, array $messageData): void
    {
        $message = CloudMessage::fromArray($messageData);

        if ($expected === null) {
            $this->assertMessageHasNoPushType($message);
        } else {
            $this->assertMessageHasPushType($message, $expected);
        }
    }

    /**
     * @return iterable<string, array{0: non-empty-string|null, 1: array<mixed>}>
     */
    public static function provideMessagesWithExpectedPushType(): iterable
    {
        yield 'message data at root level -> background' => [
            'background',
            [
                'data' => [
                    'key' => 'value',
                ],
            ],
        ];

        yield 'message data at apns level -> background' => [
            'background',
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

        yield 'both message and apns data -> background' => [
            'background',
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

        yield 'notification at root level -> alert' => [
            'alert',
            [
                'notification' => [
                    'title' => 'Alert',
                ],
            ],
        ];

        yield 'notification at apns level -> alert' => [
            'alert',
            [
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'alert' => [
                                'title' => 'Alert',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield 'notification both at root apns level -> alert' => [
            'alert',
            [
                'notification' => [
                    'title' => 'Alert',
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'alert' => [
                                'title' => 'Alert',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield 'data at root, notification at apns level -> alert' => [
            'alert',
            [
                'data' => [
                    'key' => 'value',
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'alert' => [
                                'title' => 'Alert',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield 'notification at root, data at apns level -> alert' => [
            'alert',
            [
                'notification' => [
                    'title' => 'Alert',
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

        yield 'no data -> none' => [
            null,
            [
                'data' => [],
                'apns' => [
                    'payload' => [
                    ],
                ],
            ],
        ];
    }

    /**
     * @param non-empty-string $type
     */
    private function assertMessageHasPushType(Message $message, string $type): void
    {
        $processed = Json::decode(Json::encode(($this->processor)($message)), true);

        $this->assertTrue(isset($processed['apns']['headers']['apns-push-type']));
        $this->assertSame($type, $processed['apns']['headers']['apns-push-type']);
    }

    private function assertMessageHasNoPushType(Message $message): void
    {
        $processed = Json::decode(Json::encode(($this->processor)($message)), true);

        $this->assertFalse(isset($processed['apns']['headers']['apns-push-type']));
    }
}

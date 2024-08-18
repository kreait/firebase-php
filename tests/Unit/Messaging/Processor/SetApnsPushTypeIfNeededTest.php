<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging\Processor;

use Beste\Json;
use Iterator;
use Kreait\Firebase\Messaging\CloudMessage;
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
     * @param non-empty-string $expected
     * @param array<mixed> $messageData
     */
    #[DataProvider('provideMessagesWithExpectedPushType')]
    #[Test]
    public function itSetsTheExpectedPushType(string $expected, array $messageData): void
    {
        $message = CloudMessage::fromArray($messageData);

        $processed = Json::decode(Json::encode(($this->processor)($message)), true);

        $this->assertArrayHasKey('apns-push-type', $processed['apns']['headers']);
        $this->assertSame($expected, $processed['apns']['headers']['apns-push-type']);
    }

    #[Test]
    public function itDoesNotSetThePushType(): void
    {
        $message = CloudMessage::fromArray($given = ['topic' => 'test']);

        $processed = Json::decode(Json::encode(($this->processor)($message)), true);

        $this->assertSame($given, $processed);
    }

    public static function provideMessagesWithExpectedPushType(): Iterator
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
    }
}

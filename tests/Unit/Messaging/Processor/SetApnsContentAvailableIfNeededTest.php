<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging\Processor;

use Beste\Json;
use Iterator;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Processor\SetApnsContentAvailableIfNeeded;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
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
     * @param array<mixed> $messageData
     *
     * @see https://github.com/kreait/firebase-php/pull/762
     */
    #[DataProvider('provideMessagesWithExpectedContentAvailable')]
    #[Test]
    public function itSetsTheExpectedPushType(array $messageData): void
    {
        $message = CloudMessage::fromArray($messageData);

        $processed = Json::decode(Json::encode(($this->processor)($message)), true);

        $this->assertArrayHasKey('content-available', $processed['apns']['payload']['aps']);
        $this->assertSame(1, $processed['apns']['payload']['aps']['content-available']);
    }

    #[Test]
    public function itDoesNotSetThePushType(): void
    {
        $message = CloudMessage::fromArray($given = ['topic' => 'test']);

        $processed = Json::decode(Json::encode(($this->processor)($message)), true);

        $this->assertSame($given, $processed);
    }

    public static function provideMessagesWithExpectedContentAvailable(): Iterator
    {
        yield 'message data at root level' => [
            [
                'data' => [
                    'key' => 'value',
                ],
            ],
        ];

        yield 'message data at apns level' => [
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

        yield 'both message and apns data' => [
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
    }
}

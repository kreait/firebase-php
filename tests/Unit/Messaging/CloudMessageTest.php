<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Beste\Json;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Exception\Messaging\InvalidArgument;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\FcmOptions;
use Kreait\Firebase\Messaging\MessageTarget;
use Kreait\Firebase\Messaging\Notification;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class CloudMessageTest extends TestCase
{
    public function testEmptyMessage(): void
    {
        self::assertSame('[]', Json::encode(CloudMessage::new()));
    }

    public function testInvalidTargetCausesError(): void
    {
        $this->expectException(InvalidArgumentException::class);
        CloudMessage::withTarget('invalid_target', 'foo');
    }

    public function testWithChangedTarget(): void
    {
        $original = CloudMessage::withTarget(MessageTarget::TOKEN, 'bar')
            ->withData(['foo' => 'bar'])
            ->withNotification(Notification::create('title', 'body'));

        $changed = $original->withChangedTarget(MessageTarget::TOKEN, 'baz');

        $encodedOriginal = Json::decode(Json::encode($original), true);
        $encodedOriginal[MessageTarget::TOKEN] = 'baz';

        $encodedChanged = Json::decode(Json::encode($changed), true);

        self::assertSame($encodedOriginal, $encodedChanged);
    }

    public function testAnEmptyMessageHasNotTarget(): void
    {
        self::assertFalse(CloudMessage::new()->hasTarget());
    }

    public function testWithChangedFcmOptions(): void
    {
        $options = FcmOptions::create()->withAnalyticsLabel($label = 'my-label');
        $message = CloudMessage::new()->withFcmOptions($options);

        $messageData = Json::decode(Json::encode($message), true);

        self::assertArrayHasKey('fcm_options', $messageData);
        self::assertArrayHasKey('analytics_label', $messageData['fcm_options']);
        self::assertSame($label, $messageData['fcm_options']['analytics_label']);
    }

    /**
     * @dataProvider multipleTargets
     *
     * @param array<string, string> $data
     */
    public function testAMessageCanOnlyHaveOneTarget(array $data): void
    {
        $this->expectException(InvalidArgument::class);
        CloudMessage::fromArray($data);
    }

    public function testWithDefaultSounds(): void
    {
        $expected = [
            'android' => [
                'notification' => [
                    'sound' => 'default',
                ],
            ],
            'apns' => [
                'payload' => [
                    'aps' => [
                        'sound' => 'default',
                    ],
                ],
            ],
        ];

        self::assertJsonStringEqualsJsonString(
            Json::encode($expected),
            Json::encode(CloudMessage::new()->withDefaultSounds()->jsonSerialize()),
        );
    }

    public function testWithLowestPossiblePriority(): void
    {
        $message = CloudMessage::new()->withLowestPossiblePriority();

        $payload = Json::decode(Json::encode($message), true);

        self::assertArrayHasKey('android', $payload);
        self::assertArrayHasKey('priority', $payload['android']);
        self::assertSame('normal', $payload['android']['priority']);

        self::assertArrayHasKey('apns', $payload);
        self::assertArrayHasKey('headers', $payload['apns']);
        self::assertArrayHasKey('apns-priority', $payload['apns']['headers']);
        self::assertSame('5', $payload['apns']['headers']['apns-priority']);

        self::assertArrayHasKey('webpush', $payload);
        self::assertArrayHasKey('headers', $payload['webpush']);
        self::assertArrayHasKey('Urgency', $payload['webpush']['headers']);
        self::assertSame('very-low', $payload['webpush']['headers']['Urgency']);
    }

    public function testWithHighesPossiblePriority(): void
    {
        $message = CloudMessage::new()->withHighestPossiblePriority();

        $payload = Json::decode(Json::encode($message), true);

        self::assertArrayHasKey('android', $payload);
        self::assertArrayHasKey('priority', $payload['android']);
        self::assertSame('high', $payload['android']['priority']);

        self::assertArrayHasKey('apns', $payload);
        self::assertArrayHasKey('headers', $payload['apns']);
        self::assertArrayHasKey('apns-priority', $payload['apns']['headers']);
        self::assertSame('10', $payload['apns']['headers']['apns-priority']);

        self::assertArrayHasKey('webpush', $payload);
        self::assertArrayHasKey('headers', $payload['webpush']);
        self::assertArrayHasKey('Urgency', $payload['webpush']['headers']);
        self::assertSame('high', $payload['webpush']['headers']['Urgency']);
    }

    /**
     * @return array<string, array<int, array<string, string>>>
     */
    public function multipleTargets(): array
    {
        return [
            'condition and token' => [[
                MessageTarget::CONDITION => 'something',
                MessageTarget::TOKEN => 'something else',
            ]],
            'condition and topic' => [[
                MessageTarget::CONDITION => 'something',
                MessageTarget::TOPIC => 'something else',
            ]],
            'token and topic' => [[
                MessageTarget::TOKEN => 'something',
                MessageTarget::TOPIC => 'something else',
            ]],
            'all of them' => [[
                MessageTarget::CONDITION => 'something',
                MessageTarget::TOKEN => 'something else',
                MessageTarget::TOPIC => 'something even elser',
            ]],
        ];
    }
}

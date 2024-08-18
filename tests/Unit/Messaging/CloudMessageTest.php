<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Beste\Json;
use Kreait\Firebase\Exception\Messaging\InvalidArgument;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\FcmOptions;
use Kreait\Firebase\Messaging\MessageData;
use Kreait\Firebase\Messaging\MessageTarget;
use Kreait\Firebase\Messaging\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class CloudMessageTest extends TestCase
{
    #[Test]
    public function emptyMessage(): void
    {
        $this->assertSame('[]', Json::encode(CloudMessage::new()));
    }

    #[Test]
    public function withChangedTarget(): void
    {
        $original = CloudMessage::withTarget(MessageTarget::TOKEN, 'bar')
            ->withData(['foo' => 'bar'])
            ->withNotification(Notification::create('title', 'body'))
        ;

        $changed = $original->withChangedTarget(MessageTarget::TOKEN, 'baz');

        $encodedOriginal = Json::decode(Json::encode($original), true);
        $encodedOriginal[MessageTarget::TOKEN] = 'baz';

        $encodedChanged = Json::decode(Json::encode($changed), true);

        $this->assertSame($encodedOriginal, $encodedChanged);
    }

    #[Test]
    public function anEmptyMessageHasNotTarget(): void
    {
        $this->assertFalse(CloudMessage::new()->hasTarget());
    }

    #[Test]
    public function withChangedFcmOptions(): void
    {
        $options = FcmOptions::create()->withAnalyticsLabel($label = 'my-label');
        $message = CloudMessage::new()->withFcmOptions($options);

        $messageData = Json::decode(Json::encode($message), true);

        $this->assertArrayHasKey('fcm_options', $messageData);
        $this->assertArrayHasKey('analytics_label', $messageData['fcm_options']);
        $this->assertSame($label, $messageData['fcm_options']['analytics_label']);
    }

    /**
     * @param array<string, string> $data
     */
    #[DataProvider('multipleTargets')]
    #[Test]
    public function aMessageCanOnlyHaveOneTarget(array $data): void
    {
        $this->expectException(InvalidArgument::class);
        CloudMessage::fromArray($data);
    }

    #[Test]
    public function withDefaultSounds(): void
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

        $this->assertJsonStringEqualsJsonString(
            Json::encode($expected),
            Json::encode(CloudMessage::new()->withDefaultSounds()->jsonSerialize()),
        );
    }

    #[Test]
    public function withLowestPossiblePriority(): void
    {
        $message = CloudMessage::new()->withLowestPossiblePriority();

        $payload = Json::decode(Json::encode($message), true);

        $this->assertArrayHasKey('android', $payload);
        $this->assertArrayHasKey('priority', $payload['android']);
        $this->assertSame('normal', $payload['android']['priority']);

        $this->assertArrayHasKey('apns', $payload);
        $this->assertArrayHasKey('headers', $payload['apns']);
        $this->assertArrayHasKey('apns-priority', $payload['apns']['headers']);
        $this->assertSame('5', $payload['apns']['headers']['apns-priority']);

        $this->assertArrayHasKey('webpush', $payload);
        $this->assertArrayHasKey('headers', $payload['webpush']);
        $this->assertArrayHasKey('Urgency', $payload['webpush']['headers']);
        $this->assertSame('very-low', $payload['webpush']['headers']['Urgency']);
    }

    #[Test]
    public function withHighestPossiblePriority(): void
    {
        $message = CloudMessage::new()->withHighestPossiblePriority();

        $payload = Json::decode(Json::encode($message), true);

        $this->assertArrayHasKey('android', $payload);
        $this->assertArrayHasKey('priority', $payload['android']);
        $this->assertSame('high', $payload['android']['priority']);

        $this->assertArrayHasKey('apns', $payload);
        $this->assertArrayHasKey('headers', $payload['apns']);
        $this->assertArrayHasKey('apns-priority', $payload['apns']['headers']);
        $this->assertSame('10', $payload['apns']['headers']['apns-priority']);

        $this->assertArrayHasKey('webpush', $payload);
        $this->assertArrayHasKey('headers', $payload['webpush']);
        $this->assertArrayHasKey('Urgency', $payload['webpush']['headers']);
        $this->assertSame('high', $payload['webpush']['headers']['Urgency']);
    }

    /**
     * @see https://github.com/kreait/firebase-php/issues/768
     */
    #[Test]
    public function messageDataCanBeSetWithAnObjectOrAnArray(): void
    {
        $data = ['key' => 'value'];

        $fromObject = CloudMessage::new()->withData(MessageData::fromArray($data));
        $serializedFromObject = Json::decode(Json::encode($fromObject), true);
        $this->assertSame('value', $serializedFromObject['data']['key']);

        $fromArray = CloudMessage::new()->withData($data);
        $serializedFromArray = Json::decode(Json::encode($fromArray), true);
        $this->assertSame('value', $serializedFromArray['data']['key']);

        $this->assertSame($serializedFromObject, $serializedFromArray);
    }

    public static function multipleTargets(): \Iterator
    {
        yield 'condition and token' => [[
            MessageTarget::CONDITION => 'something',
            MessageTarget::TOKEN => 'something else',
        ]];
        yield 'condition and topic' => [[
            MessageTarget::CONDITION => 'something',
            MessageTarget::TOPIC => 'something else',
        ]];
        yield 'token and topic' => [[
            MessageTarget::TOKEN => 'something',
            MessageTarget::TOPIC => 'something else',
        ]];
        yield 'all of them' => [[
            MessageTarget::CONDITION => 'something',
            MessageTarget::TOKEN => 'something else',
            MessageTarget::TOPIC => 'something different',
        ]];
    }
}

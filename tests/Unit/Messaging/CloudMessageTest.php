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
        $this->assertSame('[]', \json_encode(CloudMessage::new()));
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
            ->withNotification(Notification::create('title', 'body'))
        ;

        $changed = $original->withChangedTarget(MessageTarget::TOKEN, 'baz');

        $encodedOriginal = \json_decode(Json::encode($original), true);
        $encodedOriginal[MessageTarget::TOKEN] = 'baz';

        $encodedChanged = \json_decode(Json::encode($changed), true);

        $this->assertSame($encodedOriginal, $encodedChanged);
    }

    public function testAnEmptyMessageHasNotTarget(): void
    {
        $this->assertFalse(CloudMessage::new()->hasTarget());
    }

    public function testWithChangedFcmOptions(): void
    {
        $options = FcmOptions::create()->withAnalyticsLabel($label = 'my-label');
        $message = CloudMessage::new()->withFcmOptions($options);

        $messageData = \json_decode(Json::encode($message), true);

        $this->assertArrayHasKey('fcm_options', $messageData);
        $this->assertArrayHasKey('analytics_label', $messageData['fcm_options']);
        $this->assertSame($label, $messageData['fcm_options']['analytics_label']);
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

        $this->assertJsonStringEqualsJsonString(
            \json_encode($expected),
            \json_encode(CloudMessage::new()->withDefaultSounds()->jsonSerialize())
        );
    }

    public function testWithLowestPossiblePriority(): void
    {
        $message = CloudMessage::new()->withLowestPossiblePriority();

        $payload = \json_decode(\json_encode($message), true);

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

    public function testWithHighesPossiblePriority(): void
    {
        $message = CloudMessage::new()->withHighestPossiblePriority();

        $payload = \json_decode(\json_encode($message), true);

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

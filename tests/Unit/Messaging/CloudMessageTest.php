<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Exception\Messaging\InvalidArgument;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\FcmOptions;
use Kreait\Firebase\Messaging\MessageTarget;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Util\JSON;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class CloudMessageTest extends TestCase
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
            ->withNotification(Notification::create('title', 'body'));

        $changed = $original->withChangedTarget(MessageTarget::TOKEN, 'baz');

        $encodedOriginal = \json_decode(JSON::encode($original), true);
        $encodedOriginal[MessageTarget::TOKEN] = 'baz';

        $encodedChanged = \json_decode(JSON::encode($changed), true);

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

        $messageData = \json_decode(JSON::encode($message), true);

        $this->assertArrayHasKey('fcm_options', $messageData);
        $this->assertArrayHasKey('analytics_label', $messageData['fcm_options']);
        $this->assertSame($label, $messageData['fcm_options']['analytics_label']);
    }

    /**
     * @dataProvider multipleTargets
     */
    public function testAMessageCanOnlyHaveOneTarget($data): void
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

    public function multipleTargets()
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

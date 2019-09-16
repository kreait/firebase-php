<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use DateTimeImmutable;
use Kreait\Firebase\Exception\Messaging\InvalidArgument;
use Kreait\Firebase\Exception\Messaging\InvalidMessage;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\RawMessageFromArray;
use Kreait\Firebase\Tests\IntegrationTestCase;

/**
 * @internal
 */
class MessagingTest extends IntegrationTestCase
{
    /**
     * @var Messaging
     */
    public $messaging;

    protected function setUp()
    {
        $this->messaging = self::$factory->createMessaging();
    }

    public static function createFullMessageData(): array
    {
        return [
            'notification' => [
                // https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#notification
                'title' => 'Notification title',
                'body' => 'Notification body',
                'image' => 'http://lorempixel.com/400/200/',
            ],
            'data' => [
                'key_1' => 'Value 1',
                'key_2' => 'Value 2',
            ],
            'android' => [
                // https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#androidconfig
                'ttl' => '3600s',
                'priority' => 'normal',
                'notification' => [
                    'title' => '$GOOG up 1.43% on the day',
                    'body' => '$GOOG gained 11.80 points to close at 835.67, up 1.43% on the day.',
                    'icon' => 'stock_ticker_update',
                    'color' => '#f45342',
                ],
                'fcm_options' => [
                    // https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#fcmoptions
                    'analytics_label' => 'android-specific-analytics-label',
                ],
            ],
            'apns' => [
                // https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#apnsconfig
                'headers' => [
                    'apns-priority' => '10',
                ],
                'payload' => [
                    'aps' => [
                        'alert' => [
                            'title' => '$GOOG up 1.43% on the day',
                            'body' => '$GOOG gained 11.80 points to close at 835.67, up 1.43% on the day.',
                        ],
                        'badge' => 42,
                    ],
                ],
                'fcm_options' => [
                    // https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#fcmoptions
                    'analytics_label' => 'apns-specific-analytics-label',
                ],
            ],
            'webpush' => [
                // https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#webpushconfig
                'notification' => [
                    'title' => '$GOOG up 1.43% on the day',
                    'body' => '$GOOG gained 11.80 points to close at 835.67, up 1.43% on the day.',
                    'icon' => 'https://my-server/icon.png',
                ],
                'fcm_options' => [
                    // https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#webpushfcmoptions
                    'link' => 'https://my-server/path/to/target',
                ],
            ],
            'fcm_options' => [
                // https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#fcmoptions
                'analytics_label' => 'some-analytics-label',
            ],
        ];
    }

    public function testSendMessage()
    {
        $message = self::createFullMessageData();
        $message['condition'] = "'dogs' in topics || 'cats' in topics";

        $result = $this->messaging->send($message);

        $this->assertArrayHasKey('name', $result);
    }

    public function testSendRawMessage()
    {
        $data = self::createFullMessageData();
        $data['condition'] = "'dogs' in topics || 'cats' in topics";

        $result = $this->messaging->send(new RawMessageFromArray($data));

        $this->assertArrayHasKey('name', $result);
    }

    public function testValidateValidMessage()
    {
        $message = self::createFullMessageData();
        $message['condition'] = "'dogs' in topics || 'cats' in topics";

        $result = $this->messaging->validate($message);

        $this->assertArrayHasKey('name', $result);
    }

    public function testValidateInvalidMessage()
    {
        $message = self::createFullMessageData();
        $message['token'] = 'invalid-and-non-existing-device-token';

        $this->expectException(InvalidMessage::class);
        $this->messaging->validate($message);
    }

    public function testSendMulticastWithValidAndInvalidTarget()
    {
        if (empty(self::$registrationTokens)) {
            $this->markTestSkipped();
        }

        $message = CloudMessage::fromArray([]);
        $tokens = [
            $valid = self::$registrationTokens[0],
            $invalid = 'invalid',
        ];

        $report = $this->messaging->sendMulticast($message, $tokens);

        $this->assertCount(2, $report);
        $this->assertTrue($report->hasFailures());
        $this->assertCount(1, $report->failures());
        $this->assertCount(1, $report->successes());

        $success = $report->successes()->getItems()[0];
        $this->assertSame($valid, $success->target()->value());
        $this->assertInternalType('array', $success->result());
        $this->assertArrayHasKey('name', $success->result() ?: []);

        $failure = $report->failures()->getItems()[0];
        $this->assertSame($invalid, $failure->target()->value());
        $this->assertInstanceOf(MessagingException::class, $failure->error());
    }

    /**
     * @see https://github.com/kreait/firebase-php/issues/317
     */
    public function testSendMulticastMessageToOneRecipientOnly()
    {
        if (empty(self::$registrationTokens)) {
            $this->markTestSkipped();
        }

        $report = $this->messaging->sendMulticast(CloudMessage::new(), [self::$registrationTokens[0]]);

        $this->assertCount(1, $report->successes());
    }

    public function testSendMessageToDifferentTargets()
    {
        if (empty(self::$registrationTokens)) {
            $this->markTestSkipped();
        }

        $token = self::$registrationTokens[0];
        $topic = __FUNCTION__;
        $condition = "'{$topic}' in topics";
        $invalidToken = 'invalid_token';

        $this->unsubscribeFromAllTopics($token);

        $this->messaging->subscribeToTopic($topic, $token);

        $message = CloudMessage::new()->withNotification(['title' => 'Token Notification', 'body' => 'Token body']);

        $tokenMessage = $message->withChangedTarget('token', $token);
        $topicMessage = $message->withChangedTarget('topic', $topic);
        $conditionMessage = $message->withChangedTarget('condition', $condition);
        $invalidMessage = $message->withChangedTarget('token', $invalidToken);

        $messages = [$tokenMessage, $topicMessage, $conditionMessage, $invalidMessage];

        $report = $this->messaging->sendAll($messages);

        $this->assertCount(3, $report->successes());
        $this->assertCount(1, $report->failures());
    }

    public function testManageTopicSubscriptions()
    {
        if (empty(self::$registrationTokens)) {
            $this->markTestSkipped();
        }

        $token = self::$registrationTokens[0];
        $topicName = \uniqid('topic', false);

        $this->unsubscribeFromAllTopics($token);

        $this->messaging->subscribeToTopic($topicName, $token);

        $appInstance = $this->messaging->getAppInstance($token);
        $this->assertTrue($appInstance->isSubscribedToTopic($topicName));

        $subscriptions = $appInstance->topicSubscriptions();
        $this->assertGreaterThan(0, $subscriptions->count());

        foreach ($subscriptions as $subscription) {
            $this->assertSame($subscription->topic()->value(), $topicName);
            $this->assertLessThanOrEqual(new DateTimeImmutable(), $subscription->subscribedAt());
        }

        $this->messaging->unsubscribeFromTopic($topicName, $token);
        $this->assertFalse($this->messaging->getAppInstance($token)->isSubscribedToTopic($topicName));
    }

    public function testGetAppInstance()
    {
        if (empty(self::$registrationTokens)) {
            $this->markTestSkipped();
        }

        $token = self::$registrationTokens[0];

        $appInstance = $this->messaging->getAppInstance($token);

        $this->assertSame($token, $appInstance->registrationToken()->value());
    }

    public function testGetAppInstanceWithInvalidToken()
    {
        $this->expectException(InvalidArgument::class);
        $this->messaging->getAppInstance('foo');
    }

    private function unsubscribeFromAllTopics($token)
    {
        $appInstance = $this->messaging->getAppInstance($token);
        foreach ($appInstance->topicSubscriptions() as $subscription) {
            $this->messaging->unsubscribeFromTopic($subscription->topic(), $subscription->registrationToken());
        }

        $appInstance = $this->messaging->getAppInstance($token);
        $this->assertCount(0, $appInstance->topicSubscriptions());
    }
}

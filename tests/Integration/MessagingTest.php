<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

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
    /** @var Messaging */
    public $messaging;

    protected function setUp(): void
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

    public function testSendMessage(): void
    {
        $message = self::createFullMessageData();
        $message['condition'] = "'dogs' in topics || 'cats' in topics";

        $result = $this->messaging->send($message);

        $this->assertArrayHasKey('name', $result);
    }

    public function testSendRawMessage(): void
    {
        $data = self::createFullMessageData();
        $data['condition'] = "'dogs' in topics || 'cats' in topics";

        $result = $this->messaging->send(new RawMessageFromArray($data));

        $this->assertArrayHasKey('name', $result);
    }

    public function testValidateValidMessage(): void
    {
        $message = self::createFullMessageData();
        $message['condition'] = "'dogs' in topics || 'cats' in topics";

        $result = $this->messaging->validate($message);

        $this->assertArrayHasKey('name', $result);
    }

    public function testValidateInvalidMessage(): void
    {
        $message = self::createFullMessageData();
        $message['token'] = 'invalid-and-non-existing-device-token';

        $this->expectException(InvalidMessage::class);
        $this->messaging->validate($message);
    }

    public function testSendMulticastWithValidAndInvalidTarget(): void
    {
        $message = CloudMessage::fromArray([]);
        $tokens = [
            $valid = $this->getTestRegistrationToken(),
            $invalid = 'invalid',
        ];

        $report = $this->messaging->sendMulticast($message, $tokens);

        $this->assertCount(2, $report);
        $this->assertTrue($report->hasFailures());
        $this->assertCount(1, $report->failures());
        $this->assertCount(1, $report->successes());
        $this->assertCount(1, $report->invalidTokens());
        $this->assertSame($invalid, $report->invalidTokens()[0]);

        $success = $report->successes()->getItems()[0];
        $this->assertSame($valid, $success->target()->value());
        $this->assertIsArray($success->result());
        $this->assertArrayHasKey('name', $success->result() ?: []);

        $failure = $report->failures()->getItems()[0];
        $this->assertSame($invalid, $failure->target()->value());
        $this->assertInstanceOf(MessagingException::class, $failure->error());
    }

    /**
     * @see https://github.com/kreait/firebase-php/issues/436
     */
    public function testSendMulticastMessageToTwoInvalidRecipients(): void
    {
        $message = CloudMessage::fromArray([]);
        $tokens = [
            $first = 'first',
            $second = 'second',
        ];

        $report = $this->messaging->sendMulticast($message, $tokens);

        $this->assertTrue($report->hasFailures());
        $this->assertCount(2, $report->failures());
        $this->assertCount(0, $report->successes());

        $items = $report->failures()->getItems();

        $this->assertSame($first, $items[0]->target()->value());
        $this->assertSame($second, $items[1]->target()->value());
    }

    /**
     * @see https://github.com/kreait/firebase-php/issues/317
     */
    public function testSendMulticastMessageToOneRecipientOnly(): void
    {
        $report = $this->messaging->sendMulticast(CloudMessage::new(), [$this->getTestRegistrationToken()]);

        $this->assertCount(1, $report->successes());
    }

    public function testSendMessageToDifferentTargets(): void
    {
        $token = $this->getTestRegistrationToken();
        $topic = __FUNCTION__;
        $condition = "'{$topic}' in topics";
        $invalidToken = 'invalid_token';

        $this->messaging->subscribeToTopic($topic, $token);

        $message = CloudMessage::new()->withNotification(['title' => 'Token Notification', 'body' => 'Token body']);
        $invalidMessage = new RawMessageFromArray(['invalid' => 'message']);

        $tokenMessage = $message->withChangedTarget('token', $token);
        $topicMessage = $message->withChangedTarget('topic', $topic);
        $conditionMessage = $message->withChangedTarget('condition', $condition);
        $invalidToken = $message->withChangedTarget('token', $invalidToken);

        $messages = [$tokenMessage, $topicMessage, $conditionMessage, $invalidToken, $invalidMessage];

        $report = $this->messaging->sendAll($messages);

        $this->assertCount(3, $report->successes());
        $this->assertCount(2, $report->failures());
    }

    public function testSubscribeToTopic(): void
    {
        $token = $this->getTestRegistrationToken();
        $topicName = \uniqid(__FUNCTION__, false);

        try {
            $this->assertEquals([
                $topicName => [$token => 'OK'],
            ], $this->messaging->subscribeToTopic($topicName, $token));
        } finally {
            $this->messaging->unsubscribeFromTopic($topicName, $token);
        }
    }

    public function testSubscribeToTopics(): void
    {
        $token = $this->getTestRegistrationToken();

        $topics = [
            $firstTopic = \uniqid(__FUNCTION__.'_1', false),
            $secondTopic = \uniqid(__FUNCTION__.'_2', false),
        ];

        try {
            $this->assertEquals([
                $firstTopic => [$token => 'OK'],
                $secondTopic => [$token => 'OK'],
            ], $this->messaging->subscribeToTopics($topics, $token));
        } finally {
            $this->messaging->unsubscribeFromTopics($topics, $token);
        }
    }

    public function testUnsubscribeFromTopic(): void
    {
        $token = $this->getTestRegistrationToken();
        $topicName = \uniqid('topic', false);
        $this->messaging->subscribeToTopic($topicName, $token);

        $this->assertEquals([
            $topicName => [$token => 'OK'],
        ], $this->messaging->unsubscribeFromTopic($topicName, $token));
    }

    public function testUnsubscribeFromTopics(): void
    {
        $token = $this->getTestRegistrationToken();

        $topics = [
            $firstTopic = \uniqid(__FUNCTION__.'_1', false),
            $secondTopic = \uniqid(__FUNCTION__.'_2', false),
        ];

        $this->assertEquals([
            $firstTopic => [$token => 'OK'],
            $secondTopic => [$token => 'OK'],
        ], $this->messaging->unsubscribeFromTopics($topics, $token));
    }

    public function testUnsubscribeFromAllTopics(): void
    {
        $token = $this->getTestRegistrationToken();

        $this->messaging->unsubscribeFromAllTopics($token);

        $this->assertCount(0, $this->messaging->getAppInstance($token)->topicSubscriptions());
    }

    public function testGetAppInstance(): void
    {
        $token = $this->getTestRegistrationToken();
        $appInstance = $this->messaging->getAppInstance($token);

        $this->assertSame($token, $appInstance->registrationToken()->value());
    }

    public function testGetAppInstanceWithInvalidToken(): void
    {
        $this->expectException(InvalidArgument::class);
        $this->messaging->getAppInstance('foo');
    }
}

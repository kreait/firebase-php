<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Iterator;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\Messaging\InvalidArgument;
use Kreait\Firebase\Exception\Messaging\InvalidMessage;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\RawMessageFromArray;
use Kreait\Firebase\Messaging\WebPushConfig;
use Kreait\Firebase\Tests\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 *
 * @phpstan-import-type WebPushHeadersShape from WebPushConfig
 */
final class MessagingTest extends IntegrationTestCase
{
    public Messaging $messaging;

    protected function setUp(): void
    {
        $this->messaging = self::$factory->createMessaging();
    }

    /**
     * @return array<string, mixed>
     */
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
                    'title' => '$GOOGLE up 1.43% on the day',
                    'body' => '$GOOGLE gained 11.80 points to close at 835.67, up 1.43% on the day.',
                    'icon' => 'stock_ticker_update',
                    'color' => '#f45342',
                    'sound' => 'default',
                    'default_sound' => true,
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
                            'title' => '$GOOGLE up 1.43% on the day',
                            'body' => '$GOOGLE gained 11.80 points to close at 835.67, up 1.43% on the day.',
                        ],
                        'sound' => 'default',
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
                'headers' => [
                    'Urgency' => 'normal',
                ],
                'notification' => [
                    'title' => '$GOOGLE up 1.43% on the day',
                    'body' => '$GOOGLE gained 11.80 points to close at 835.67, up 1.43% on the day.',
                    'icon' => 'https://my-server.example/icon.png',
                ],
                'fcm_options' => [
                    // https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#webpushfcmoptions
                    'link' => 'https://my-server.example/path/to/target',
                ],
            ],
            'fcm_options' => [
                // https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#fcmoptions
                'analytics_label' => 'some-analytics-label',
            ],
        ];
    }

    #[Test]
    public function sendMessage(): void
    {
        $message = self::createFullMessageData();
        $message['condition'] = "'dogs' in topics || 'cats' in topics";

        $result = $this->messaging->send($message);

        $this->assertArrayHasKey('name', $result);
    }

    #[Test]
    public function sendRawMessage(): void
    {
        $data = self::createFullMessageData();
        $data['condition'] = "'dogs' in topics || 'cats' in topics";

        $result = $this->messaging->send(new RawMessageFromArray($data));

        $this->assertArrayHasKey('name', $result);
    }

    /**
     * @see https://github.com/kreait/firebase-php/issues/591
     */
    #[Test]
    public function sendingAMessageWithEmptyMessageDataShouldNotFail(): void
    {
        $message = CloudMessage::new()
            ->withData([])
            ->toToken($this->getTestRegistrationToken());
        ;

        $this->messaging->send($message);
        $this->addToAssertionCount(1);
    }

    /**
     * @param non-empty-string $keyword
     */
    #[DataProvider('reservedKeywordsThatStillAreAccepted')]
    #[Test]
    public function sendMessageWithReservedKeywordInMessageDataThatIsStillAccepted(string $keyword): void
    {
        $message = CloudMessage::new()
            ->withData([$keyword => 'value'])
            ->toToken($this->getTestRegistrationToken());
        ;

        $this->messaging->send($message);
        $this->addToAssertionCount(1);
    }

    public static function reservedKeywordsThatStillAreAccepted(): Iterator
    {
        yield 'notification' => ['notification'];
    }

    #[Test]
    public function validateValidMessage(): void
    {
        $message = self::createFullMessageData();
        $message['condition'] = "'dogs' in topics || 'cats' in topics";

        $result = $this->messaging->validate($message);

        $this->assertArrayHasKey('name', $result);
    }

    #[Test]
    public function validateInvalidMessage(): void
    {
        $message = self::createFullMessageData();
        $message['token'] = 'invalid-and-non-existing-device-token';

        $this->expectException(InvalidMessage::class);
        $this->messaging->validate($message);
    }

    #[Test]
    public function sendMulticastWithValidAndInvalidTarget(): void
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
        $this->assertTrue($failure->messageWasInvalid());
        $this->assertInstanceOf(MessagingException::class, $failure->error());
    }

    /**
     * @see https://github.com/kreait/firebase-php/issues/436
     */
    #[Test]
    public function sendMulticastMessageToTwoInvalidRecipients(): void
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
    #[Test]
    public function sendMulticastMessageToOneRecipientOnly(): void
    {
        $report = $this->messaging->sendMulticast(CloudMessage::new(), [$this->getTestRegistrationToken()]);

        $this->assertCount(1, $report->successes());
    }

    #[Test]
    public function sendMessageToDifferentTargets(): void
    {
        $token = $this->getTestRegistrationToken();
        $topic = __FUNCTION__;
        $condition = "'{$topic}' in topics";
        $invalidToken = 'invalid_token';

        $this->messaging->subscribeToTopic($topic, $token);

        $message = CloudMessage::new()->withNotification(['title' => 'Token Notification', 'body' => 'Token body']);
        $invalidMessage = new RawMessageFromArray(['invalid' => 'message']);

        $tokenMessage = $message->toToken($token);
        $topicMessage = $message->toTopic($topic);
        $conditionMessage = $message->toCondition($condition);
        $invalidToken = $message->toToken($invalidToken);

        $messages = [$tokenMessage, $topicMessage, $conditionMessage, $invalidToken, $invalidMessage];

        $report = $this->messaging->sendAll($messages);

        $this->assertCount(3, $report->successes());
        $this->assertCount(2, $report->failures());
    }

    #[Test]
    public function validateRegistrationTokens(): void
    {
        $tokens = [
            $valid = $this->getTestRegistrationToken(),
            // we don't have an unknown token
            $invalid = 'invalid',
        ];

        $result = $this->messaging->validateRegistrationTokens($tokens);

        $this->assertSame($valid, $result['valid'][0]);
        $this->assertSame($invalid, $result['invalid'][0]);
    }

    #[Test]
    public function subscribeToTopic(): void
    {
        $token = $this->getTestRegistrationToken();
        $topicName = self::randomString(__FUNCTION__);

        try {
            $this->assertSame([
                $topicName => [$token => 'OK'],
            ], $this->messaging->subscribeToTopic($topicName, $token));
        } finally {
            $this->messaging->unsubscribeFromTopic($topicName, $token);
        }
    }

    #[Test]
    public function subscribeToTopics(): void
    {
        $token = $this->getTestRegistrationToken();

        $topics = [
            $firstTopic = self::randomString(__FUNCTION__),
            $secondTopic = self::randomString(__FUNCTION__),
        ];

        try {
            $this->assertEqualsCanonicalizing([
                $firstTopic => [$token => 'OK'],
                $secondTopic => [$token => 'OK'],
            ], $this->messaging->subscribeToTopics($topics, $token));
        } finally {
            $this->messaging->unsubscribeFromTopics($topics, $token);
        }
    }

    #[Test]
    public function unsubscribeFromTopic(): void
    {
        $token = $this->getTestRegistrationToken();
        $topicName = self::randomString(__FUNCTION__);
        $this->messaging->subscribeToTopic($topicName, $token);

        $this->assertSame([
            $topicName => [$token => 'OK'],
        ], $this->messaging->unsubscribeFromTopic($topicName, $token));
    }

    #[Test]
    public function unsubscribeFromTopics(): void
    {
        $token = $this->getTestRegistrationToken();

        $topics = [
            $firstTopic = self::randomString(__FUNCTION__.'_1'),
            $secondTopic = self::randomString(__FUNCTION__.'_2'),
        ];

        $this->assertEqualsCanonicalizing([
            $firstTopic => [$token => 'OK'],
            $secondTopic => [$token => 'OK'],
        ], $this->messaging->unsubscribeFromTopics($topics, $token));
    }

    #[Test]
    public function getAppInstance(): void
    {
        $token = $this->getTestRegistrationToken();
        $appInstance = $this->messaging->getAppInstance($token);

        $this->assertSame($token, $appInstance->registrationToken()->value());
    }

    #[Test]
    public function getAppInstanceWithInvalidToken(): void
    {
        $this->expectException(InvalidArgument::class);
        $this->messaging->getAppInstance('foo');
    }

    #[Test]
    public function sendMessageToUnknownToken(): void
    {
        $this->expectException(NotFound::class);

        try {
            $this->messaging->send(['token' => self::$unknownToken]);
        } catch (NotFound $e) {
            $this->assertNotEmpty($e->errors());

            throw $e;
        }
    }

    #[Test]
    public function getAppInstanceForUnknownToken(): void
    {
        $this->expectException(NotFound::class);

        try {
            $this->messaging->getAppInstance(self::$unknownToken);
        } catch (NotFound $e) {
            $this->assertNotEmpty($e->errors());

            throw $e;
        }
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function sendWebPushNotificationWithAnEmptyTitle(): void
    {
        $message = CloudMessage::new()
            ->withWebPushConfig(WebPushConfig::fromArray([
                'notification' => [
                    'title' => '',
                ],
            ]))
            ->toToken($this->getTestRegistrationToken());

        $this->messaging->send($message);
    }
}

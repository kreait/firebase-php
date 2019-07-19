<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Tests\IntegrationTestCase;

abstract class MessageTestCase extends IntegrationTestCase
{
    /**
     * @var Messaging
     */
    public $messaging;

    /**
     * @var array
     */
    public $fullMessageData;

    protected function setUp()
    {
        $this->messaging = self::$firebase->getMessaging();

        $this->fullMessageData = self::createFullMessageData();
    }

    protected function assertSuccessfulMessage($message)
    {
        $result = $this->messaging->send($message);

        $this->assertTrue($noExceptionHasBeenThrown = true);

        return $result;
    }

    abstract public function testSendEmptyMessage();

    public function testSendFullMessage()
    {
        $message = CloudMessage::fromArray($this->fullMessageData);
        $this->assertSuccessfulMessage($message);
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
            ],
            'webpush' => [
                // https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#webpushconfig
                'notification' => [
                    'title' => '$GOOG up 1.43% on the day',
                    'body' => '$GOOG gained 11.80 points to close at 835.67, up 1.43% on the day.',
                    'icon' => 'https://my-server/icon.png',
                ],
            ],
            'fcm_options' => [
                // https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#fcmoptions
                'analytics_label' => 'some-analytics-label',
            ],
        ];
    }
}

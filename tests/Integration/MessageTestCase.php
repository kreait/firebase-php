<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Kreait\Firebase\Messaging;
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
        $this->messaging = static::$firebase->getMessaging();

        $this->fullMessageData = static::createFullMessageData();
    }

    protected function assertSuccessfulMessage($message)
    {
        $this->messaging->send($message);
        $this->addToAssertionCount(1);
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
}

<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Tests\IntegrationTestCase;

abstract class MessagingTestCase extends IntegrationTestCase
{
    /**
     * @var Messaging
     */
    public $messaging;

    /**
     * @var Notification
     */
    public $notification;

    /**
     * @var array
     */
    public $data;

    protected function setUp()
    {
        $this->messaging = self::$firebase->getMessaging();

        $this->notification = Notification::fromArray([
            'title' => 'Notification title',
            'body' => 'Notification body',
        ]);

        $this->data = [
            'key_1' => 'Value 1',
            'key_2' => 'Value 2',
        ];
    }

    protected function assertSuccessfulMessage($message)
    {
        $result = $this->messaging->send($message);

        $this->assertTrue($noExceptionHasBeenThrown = true);

        return $result;
    }

    abstract public function testSendEmptyMessage();

    abstract public function testSendMessageWithData();

    abstract public function testSendMessageWithNotification();

    abstract public function testSendMessageWithNotificationAndData();
}

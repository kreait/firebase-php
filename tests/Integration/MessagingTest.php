<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Kreait\Firebase\Messaging;
use Kreait\Firebase\Tests\IntegrationTestCase;

class MessagingTest extends IntegrationTestCase
{
    /**
     * @var Messaging
     */
    public $messaging;

    protected function setUp()
    {
        $this->messaging = self::$firebase->getMessaging();
    }

    public function testSendMessage()
    {
        $message = MessageTestCase::createFullMessageData();
        $message['condition'] = "'dogs' in topics || 'cats' in topics";

        $this->messaging->send($message);
        $this->assertTrue($noExceptionHasBeenThrown = true);
    }

    public function testSubscribeToTopic()
    {
        $this->messaging->subscribeToTopic('foo', self::$registrationTokens);
        $this->assertTrue($noExceptionHasBeenThrown = true);
    }

    public function testUnsubscribeFromTopic()
    {
        $this->messaging->unsubscribeFromTopic('foo', self::$registrationTokens);
        $this->assertTrue($noExceptionHasBeenThrown = true);
    }
}

<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Kreait\Firebase\Exception\Messaging\InvalidMessage;
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

    public function testValidateValidMessage()
    {
        $message = MessageTestCase::createFullMessageData();
        $message['condition'] = "'dogs' in topics || 'cats' in topics";

        $this->messaging->validate($message);
        $this->assertTrue($noExceptionHasBeenThrown = true);
    }

    public function testValidateInvalidMessage()
    {
        $message = MessageTestCase::createFullMessageData();
        $message['token'] = 'invalid-and-non-existing-device-token';

        $this->expectException(InvalidMessage::class);
        $this->messaging->validate($message);
    }

    public function testSubscribeToTopic()
    {
        if (empty(self::$registrationTokens)) {
            $this->markTestSkipped();
        }

        $this->messaging->subscribeToTopic('foo', self::$registrationTokens);
        $this->assertTrue($noExceptionHasBeenThrown = true);
    }

    public function testUnsubscribeFromTopic()
    {
        if (empty(self::$registrationTokens)) {
            $this->markTestSkipped();
        }

        $this->messaging->unsubscribeFromTopic('foo', self::$registrationTokens);
        $this->assertTrue($noExceptionHasBeenThrown = true);
    }
}

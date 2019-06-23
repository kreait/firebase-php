<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Kreait\Firebase\Exception\Messaging\InvalidMessage;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
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

        $result = $this->messaging->send($message);

        $this->assertArrayHasKey('name', $result);
    }

    public function testValidateValidMessage()
    {
        $message = MessageTestCase::createFullMessageData();
        $message['condition'] = "'dogs' in topics || 'cats' in topics";

        $result = $this->messaging->validate($message);

        $this->assertArrayHasKey('name', $result);
    }

    public function testValidateInvalidMessage()
    {
        $message = MessageTestCase::createFullMessageData();
        $message['token'] = 'invalid-and-non-existing-device-token';

        $this->expectException(InvalidMessage::class);
        $this->messaging->validate($message);
    }

    public function testSendMulticastWithValidAndInvalidTarget()
    {
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
        $this->assertInternalType('string', $success->result());
        $this->assertArrayHasKey('name', $success->result() ?: []);

        $failure = $report->failures()->getItems()[0];
        $this->assertSame($invalid, $failure->target()->value());
        $this->assertInstanceOf(MessagingException::class, $failure->error());
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

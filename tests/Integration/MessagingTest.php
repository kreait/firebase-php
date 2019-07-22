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

    public function testSendRawMessage()
    {
        $data = MessageTestCase::createFullMessageData();
        $data['condition'] = "'dogs' in topics || 'cats' in topics";

        $result = $this->messaging->send(new RawMessageFromArray($data));

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

    public function testSubscribeToTopic()
    {
        if (empty(self::$registrationTokens)) {
            $this->markTestSkipped();
        }

        $this->messaging->subscribeToTopic('foo', self::$registrationTokens);
        $this->addToAssertionCount(1);
    }

    public function testUnsubscribeFromTopic()
    {
        if (empty(self::$registrationTokens)) {
            $this->markTestSkipped();
        }

        $this->messaging->unsubscribeFromTopic('foo', self::$registrationTokens);
        $this->addToAssertionCount(1);
    }

    public function testChangeTopicSubscription()
    {
        if (empty(self::$registrationTokens)) {
            $this->markTestSkipped();
        }

        $topicName = \uniqid('topic', false);
        $token = Messaging\RegistrationToken::fromValue(self::$registrationTokens[0]);

        $this->messaging->subscribeToTopic($topicName, $token);
        $this->assertTrue($this->messaging->getAppInstance($token)->isSubscribedToTopic($topicName));

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
}

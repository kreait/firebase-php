<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit;

use GuzzleHttp\Psr7\Response;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Exception\Messaging\InvalidArgument;
use Kreait\Firebase\Exception\Messaging\InvalidMessage;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\ApiClient;
use Kreait\Firebase\Messaging\AppInstanceApiClient;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
class MessagingTest extends UnitTestCase
{
    private $messagingApi;
    private $appInstanceApi;

    /** @var Messaging */
    private $messaging;

    protected function setUp()
    {
        $this->messagingApi = $this->createMock(ApiClient::class);
        $this->appInstanceApi = $this->createMock(AppInstanceApiClient::class);

        $this->messaging = new Messaging($this->messagingApi, $this->appInstanceApi);
    }

    public function testSendInvalidObject()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->messaging->send(new \stdClass());
    }

    public function testSendInvalidArray()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->messaging->send([]);
    }

    /**
     * @dataProvider validTokenProvider
     */
    public function testSubscribeToTopicWithValidTokens($tokens)
    {
        $this->appInstanceApi->expects($this->once())
            ->method($this->anything())
            ->willReturn(new Response(200, [], '[]'));

        $this->messaging->subscribeToTopic('topic', $tokens);
        $this->assertTrue($noExceptionHasBeenThrown = true);
    }

    /**
     * @dataProvider invalidTokenProvider
     */
    public function testSubscribeToTopicWithInvalidTokens($tokens)
    {
        $this->expectException(InvalidArgument::class);
        $this->messaging->subscribeToTopic('topic', $tokens);
    }

    /**
     * @dataProvider validTokenProvider
     */
    public function testUnsubscribeFromTopicWithValidTokens($tokens)
    {
        $this->appInstanceApi->expects($this->once())
            ->method($this->anything())
            ->willReturn(new Response(200, [], '[]'));

        $this->messaging->unsubscribeFromTopic('topic', $tokens);
        $this->assertTrue($noExceptionHasBeenThrown = true);
    }

    /**
     * @dataProvider invalidTokenProvider
     */
    public function testUnsubscribeFromTopicWithInvalidTokens($tokens)
    {
        $this->expectException(InvalidArgument::class);
        $this->messaging->unsubscribeFromTopic('topic', $tokens);
    }

    public function testValidateMessageGivenAnInvalidArgument()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->messaging->validate('string');
    }

    public function testValidateMessageGivenAnUnknownDeviceToken()
    {
        $message = CloudMessage::withTarget(Messaging\MessageTarget::TOKEN, 'foo');
        $e = $this->createMock(NotFound::class);
        $e->method('response')->willReturn(new Response());

        $this->messagingApi
            ->method('validateMessage')
            ->with($message)
            ->willThrowException(new NotFound());

        $this->expectException(InvalidMessage::class);
        $this->messaging->validate($message);
    }

    public function testItWillNotSendAMessageWithoutATarget()
    {
        $message = CloudMessage::new();

        $this->assertFalse($message->hasTarget());

        $this->expectException(InvalidArgumentException::class);
        $this->messaging->send($message);
    }

    public function testItDoesNotAcceptInvalidMessagesWhenMulticasting()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->messaging->sendMulticast(new \stdClass(), []);
    }

    public function testItHandlesArraysWhenMulticasting()
    {
        $this->messaging->sendMulticast([], []);
        $this->assertTrue($noExceptionHasBeenThrown = true);
    }

    public function testItHandlesNonCloudMessagesWhenMulticasting()
    {
        $message = new class() implements Messaging\Message {
            public function jsonSerialize()
            {
                return [];
            }
        };

        $this->messaging->sendMulticast($message, []);
        $this->assertTrue($noExceptionHasBeenThrown = true);
    }

    public function validTokenProvider()
    {
        return [
            ['foo'],
            [['foo']],
            [Messaging\RegistrationToken::fromValue('foo')],
            [[Messaging\RegistrationToken::fromValue('foo')]],
        ];
    }

    public function invalidTokenProvider()
    {
        return [
            [null],
            [[]],
            [1],
        ];
    }
}

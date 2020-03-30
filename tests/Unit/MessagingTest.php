<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Exception\Messaging\InvalidArgument;
use Kreait\Firebase\Exception\Messaging\InvalidMessage;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\ApiClient;
use Kreait\Firebase\Messaging\AppInstanceApiClient;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\RegistrationToken;
use Kreait\Firebase\Project\ProjectId;
use Kreait\Firebase\Tests\UnitTestCase;
use stdClass;

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

        $this->messaging = new Messaging($this->messagingApi, $this->appInstanceApi, ProjectId::fromString('project-id'));
    }

    public function testDetermineProjectIdFromClientConfig()
    {
        $httpClient = new Client(['base_uri' => 'https://fcm.googleapis.com/v1/projects/project-id']);
        $apiClient = new ApiClient($httpClient);

        new Messaging($apiClient, $this->appInstanceApi);
        $this->addToAssertionCount(1);
    }

    public function testWithUndeterminableProjectId()
    {
        $this->expectException(\InvalidArgumentException::class);
        new Messaging($this->messagingApi, $this->appInstanceApi);
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

    public function testSubscribeToTopicWithInvalidTokens()
    {
        $this->expectException(InvalidArgument::class);
        $this->messaging->subscribeToTopic('topic', new stdClass());
    }

    public function testSubscribeToTopicWithEmptyTokenList()
    {
        $this->expectException(InvalidArgument::class);
        $this->messaging->subscribeToTopic('topic', []);
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

    public function testUnsubscribeFromTopicWithEmptyTokenList()
    {
        $this->expectException(InvalidArgument::class);
        $this->messaging->unsubscribeFromTopic('topic', []);
    }

    public function testValidateMessageGivenAnInvalidArgument()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->messaging->validate('string');
    }

    public function testValidateMessageGivenAnUnknownDeviceToken()
    {
        $message = CloudMessage::withTarget(Messaging\MessageTarget::TOKEN, 'foo');

        $this->messagingApi
            ->method('send')
            ->willThrowException((new NotFound()));

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

    public function testAMulticastMessageCannotBeTooLarge()
    {
        $tokens = \array_fill(0, 501, 'token');

        $this->expectException(InvalidArgumentException::class);
        $this->messaging->sendMulticast(CloudMessage::new(), $tokens);
    }

    public function testSendAllCannotBeTooLarge()
    {
        $messages = \array_fill(0, 501, CloudMessage::new());

        $this->expectException(InvalidArgumentException::class);
        $this->messaging->sendAll($messages);
    }

    public function validTokenProvider()
    {
        return [
            ['foo'],
            [['foo']],
            [RegistrationToken::fromValue('foo')],
            [[RegistrationToken::fromValue('foo')]],
        ];
    }
}

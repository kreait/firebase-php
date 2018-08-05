<?php

namespace Kreait\Firebase\Tests\Unit;

use GuzzleHttp\Psr7\Response;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Exception\Messaging\InvalidArgument;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\ApiClient;
use Kreait\Firebase\Messaging\TopicManagementApiClient;
use Kreait\Firebase\Tests\UnitTestCase;

class MessagingTest extends UnitTestCase
{
    /**
     * @var Messaging
     */
    private $messaging;

    /**
     * @var ApiClient
     */
    private $messagingApi;

    /**
     * @var TopicManagementApiClient
     */
    private $topicManagementApi;

    protected function setUp()
    {
        $this->messagingApi = $this->createMock(ApiClient::class);
        $this->topicManagementApi = $this->createMock(TopicManagementApiClient::class);

        $this->messaging = new Messaging($this->messagingApi, $this->topicManagementApi);
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
        $this->topicManagementApi->expects($this->once())
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
        $this->topicManagementApi->expects($this->once())
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

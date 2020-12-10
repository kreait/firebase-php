<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Exception\Messaging\InvalidArgument;
use Kreait\Firebase\Exception\MessagingApiExceptionConverter;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\ApiClient;
use Kreait\Firebase\Messaging\AppInstanceApiClient;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\RegistrationToken;
use Kreait\Firebase\Project\ProjectId;
use Kreait\Firebase\Tests\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;

/**
 * @internal
 */
class MessagingTest extends UnitTestCase
{
    /** @var ApiClient&MockObject */
    private $messagingApi;

    /** @var AppInstanceApiClient&MockObject */
    private $appInstanceApi;

    /** @var MessagingApiExceptionConverter&MockObject */
    private $errorHandler;

    /** @var Messaging */
    private $messaging;

    protected function setUp(): void
    {
        $this->messagingApi = $this->createMock(ApiClient::class);
        $this->appInstanceApi = $this->createMock(AppInstanceApiClient::class);
        $this->errorHandler = $this->createMock(MessagingApiExceptionConverter::class);

        $this->messaging = new Messaging(ProjectId::fromString('project-id'), $this->messagingApi, $this->appInstanceApi);
    }

    public function testSendInvalidArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->messaging->send([]);
    }

    public function testSubscribeToTopicWithInvalidTokens(): void
    {
        $this->expectException(InvalidArgument::class);
        $this->messaging->subscribeToTopic('topic', new stdClass());
    }

    public function testSubscribeToTopicWithEmptyTokenList(): void
    {
        $this->expectException(InvalidArgument::class);
        $this->messaging->subscribeToTopic('topic', []);
    }

    public function testUnsubscribeFromTopicWithEmptyTokenList(): void
    {
        $this->expectException(InvalidArgument::class);
        $this->messaging->unsubscribeFromTopic('topic', []);
    }

    public function testItWillNotSendAMessageWithoutATarget(): void
    {
        $message = CloudMessage::new();

        $this->assertFalse($message->hasTarget());

        $this->expectException(InvalidArgumentException::class);
        $this->messaging->send($message);
    }

    public function testAMulticastMessageCannotBeTooLarge(): void
    {
        $tokens = \array_fill(0, 501, 'token');

        $this->expectException(InvalidArgumentException::class);
        $this->messaging->sendMulticast(CloudMessage::new(), $tokens);
    }

    public function testSendAllCannotBeTooLarge(): void
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

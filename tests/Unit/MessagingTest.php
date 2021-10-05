<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Exception\Messaging\InvalidArgument;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\ApiClient;
use Kreait\Firebase\Messaging\AppInstanceApiClient;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Project\ProjectId;
use Kreait\Firebase\Tests\UnitTestCase;
use stdClass;

/**
 * @internal
 */
final class MessagingTest extends UnitTestCase
{
    private Messaging $messaging;

    protected function setUp(): void
    {
        $messagingApi = $this->createMock(ApiClient::class);
        $appInstanceApi = $this->createMock(AppInstanceApiClient::class);

        $this->messaging = new Messaging(ProjectId::fromString('project-id'), $messagingApi, $appInstanceApi);
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
}

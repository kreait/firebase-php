<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Exception\Messaging\InvalidArgument;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\ApiClient;
use Kreait\Firebase\Messaging\AppInstanceApiClient;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\Test;

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

        $this->messaging = new Messaging($messagingApi, $appInstanceApi);
    }

    #[Test]
    public function sendInvalidArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->messaging->send([]);
    }

    #[Test]
    public function subscribeToTopicWithEmptyTokenList(): void
    {
        $this->expectException(InvalidArgument::class);
        $this->messaging->subscribeToTopic('topic', []);
    }

    #[Test]
    public function unsubscribeFromTopicWithEmptyTokenList(): void
    {
        $this->expectException(InvalidArgument::class);
        $this->messaging->unsubscribeFromTopic('topic', []);
    }

    #[Test]
    public function itWillNotSendAMessageWithoutATarget(): void
    {
        $message = CloudMessage::new();

        $this->assertFalse($message->hasTarget());

        $this->expectException(InvalidArgumentException::class);
        $this->messaging->send($message);
    }
}

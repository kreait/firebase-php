<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Messaging;

use Kreait\Firebase\Exception\Messaging\InvalidArgument;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Messaging\MessageToRegistrationToken;
use Kreait\Firebase\Tests\Integration\MessagingTestCase;

class MessageToRegistrationTokenTest extends MessagingTestCase
{
    /**
     * @var string
     */
    private $token;

    protected function setUp()
    {
        parent::setUp();
        $this->token = 'registration-token';
    }

    public function testSendMessageToUnknownRegistrationToken()
    {
        $this->expectException(InvalidArgument::class);

        $this->messaging->send(MessageToRegistrationToken::create('unknown-token'));
    }

    public function testSendEmptyMessage()
    {
        $this->markTestSkipped('No valid registration token available');

        $this->assertSuccessfulMessage(MessageToRegistrationToken::create($this->token));
    }

    public function testSendMessageWithData()
    {
        $this->markTestSkipped('No valid registration token available');

        $this->assertSuccessfulMessage(
            MessageToRegistrationToken::create($this->token)
                ->withData($this->data)
        );
    }

    public function testSendMessageWithNotification()
    {
        $this->markTestSkipped('No valid registration token available');

        $this->assertSuccessfulMessage(
            MessageToRegistrationToken::create($this->token)
                ->withNotification($this->notification)
        );
    }

    public function testSendMessageWithNotificationAndData()
    {
        $this->markTestSkipped('No valid registration token available');

        $this->assertSuccessfulMessage(
            MessageToRegistrationToken::create($this->token)
                ->withNotification($this->notification)
                ->withData($this->data)
        );
    }
}

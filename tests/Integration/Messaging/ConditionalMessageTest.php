<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Messaging;

use Kreait\Firebase\Messaging\ConditionalMessage;
use Kreait\Firebase\Tests\Integration\MessagingTestCase;

class ConditionalMessageTest extends MessagingTestCase
{
    private $condition;

    protected function setUp()
    {
        parent::setUp();

        $this->condition = "'foo' in topics || 'cats' in topics";
    }

    public function testSendEmptyMessage()
    {
        $this->assertSuccessfulMessage(ConditionalMessage::create($this->condition));
    }

    public function testSendMessageWithData()
    {
        $this->assertSuccessfulMessage(
            ConditionalMessage::create($this->condition)
                ->withData($this->data)
        );
    }

    public function testSendMessageWithNotification()
    {
        $this->assertSuccessfulMessage(
            ConditionalMessage::create($this->condition)
                ->withNotification($this->notification)
        );
    }

    public function testSendMessageWithNotificationAndData()
    {
        $this->assertSuccessfulMessage(
            ConditionalMessage::create($this->condition)
                ->withNotification($this->notification)
                ->withData($this->data)
        );
    }
}

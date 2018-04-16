<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Messaging;

use Kreait\Firebase\Messaging\MessageToTopic;
use Kreait\Firebase\Tests\Integration\MessagingTestCase;

class MessageToTopicTest extends MessagingTestCase
{
    private $topic;

    protected function setUp()
    {
        parent::setUp();
        $this->topic = 'integration-test-topic';
    }

    public function testSendEmptyMessage()
    {
        $this->assertSuccessfulMessage(MessageToTopic::create($this->topic));
    }

    public function testSendMessageWithData()
    {
        $this->assertSuccessfulMessage(
            MessageToTopic::create($this->topic)
                ->withData($this->data)
        );
    }

    public function testSendMessageWithNotification()
    {
        $this->assertSuccessfulMessage(
            MessageToTopic::create($this->topic)
                ->withNotification($this->notification)
        );
    }

    public function testSendMessageWithNotificationAndData()
    {
        $this->assertSuccessfulMessage(
            MessageToTopic::create($this->topic)
                ->withNotification($this->notification)
                ->withData($this->data)
        );
    }
}

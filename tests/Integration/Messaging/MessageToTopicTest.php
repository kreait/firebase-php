<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Messaging\MessageToTopic;
use Kreait\Firebase\Tests\Integration\MessageTestCase;

/**
 * @internal
 */
class MessageToTopicTest extends MessageTestCase
{
    private $topic;

    protected function setUp()
    {
        parent::setUp();

        $this->topic = 'integration-test-topic';
        $this->fullMessageData['topic'] = $this->topic;
    }

    public function testWithoutTopic()
    {
        unset($this->fullMessageData['topic']);
        $this->expectException(InvalidArgumentException::class);
        MessageToTopic::fromArray($this->fullMessageData);
    }

    public function testSendFullMessage()
    {
        $message = MessageToTopic::fromArray($this->fullMessageData);

        $this->assertEquals($this->topic, $message->topic());
        $this->assertSuccessfulMessage($message);
    }

    public function testSendEmptyMessage()
    {
        $this->assertSuccessfulMessage(MessageToTopic::create($this->topic));
    }
}

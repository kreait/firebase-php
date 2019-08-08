<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Messaging\MessageToTopic;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 *
 * @deprecated 4.29.0
 */
class MessageToTopicTest extends UnitTestCase
{
    public function testCreate()
    {
        $message = MessageToTopic::create('topic');

        $this->assertInstanceOf(MessageToTopic::class, $message);
        $this->assertSame('topic', $message->topic());
    }

    public function testCreateWithoutTopic()
    {
        $this->expectException(InvalidArgumentException::class);
        MessageToTopic::fromArray([]);
    }

    public function testCreateFromArray()
    {
        $message = MessageToTopic::fromArray(['topic' => 'topic']);

        $this->assertInstanceOf(MessageToTopic::class, $message);
        $this->assertSame('topic', $message->topic());
    }

    public function testCreateFromArrayWithoutTopic()
    {
        $this->expectException(InvalidArgumentException::class);
        MessageToTopic::fromArray([]);
    }
}

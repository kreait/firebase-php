<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Messaging\ConditionalMessage;
use Kreait\Firebase\Tests\Integration\MessageTestCase;

/**
 * @internal
 */
class ConditionalMessageTest extends MessageTestCase
{
    private $condition;

    protected function setUp()
    {
        parent::setUp();

        $this->condition = "'dogs' in topics || 'cats' in topics";
        $this->fullMessageData['condition'] = $this->condition;
    }

    public function testWithoutToken()
    {
        unset($this->fullMessageData['condition']);
        $this->expectException(InvalidArgumentException::class);
        ConditionalMessage::fromArray($this->fullMessageData);
    }

    public function testSendFullMessage()
    {
        $message = ConditionalMessage::fromArray($this->fullMessageData);
        $this->assertSuccessfulMessage($message);
    }

    public function testSendEmptyMessage()
    {
        $this->assertSuccessfulMessage(ConditionalMessage::create($this->condition));
    }
}

<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Messaging\ConditionalMessage;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 *
 * @deprecated 4.29.0
 */
class ConditionalMessageTest extends UnitTestCase
{
    public function testCreate()
    {
        $message = ConditionalMessage::create('condition');

        $this->assertInstanceOf(ConditionalMessage::class, $message);
        $this->assertSame('condition', $message->condition());
    }

    public function testCreateWithoutCondition()
    {
        $this->expectException(InvalidArgumentException::class);
        ConditionalMessage::fromArray([]);
    }

    public function testCreateFromArray()
    {
        $message = ConditionalMessage::fromArray(['condition' => 'condition']);

        $this->assertInstanceOf(ConditionalMessage::class, $message);
        $this->assertSame('condition', $message->condition());
    }

    public function testCreateFromArrayWithoutCondition()
    {
        $this->expectException(InvalidArgumentException::class);
        ConditionalMessage::fromArray([]);
    }
}

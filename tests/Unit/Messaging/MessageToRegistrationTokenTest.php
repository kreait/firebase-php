<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Messaging\MessageToRegistrationToken;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 *
 * @deprecated 4.29.0
 */
class MessageToRegistrationTokenTest extends UnitTestCase
{
    public function testCreate()
    {
        $message = MessageToRegistrationToken::create('token');

        $this->assertInstanceOf(MessageToRegistrationToken::class, $message);
        $this->assertSame('token', $message->token());
    }

    public function testCreateWithoutToken()
    {
        $this->expectException(InvalidArgumentException::class);
        MessageToRegistrationToken::fromArray([]);
    }

    public function testCreateFromArray()
    {
        $message = MessageToRegistrationToken::fromArray(['token' => 'token']);

        $this->assertInstanceOf(MessageToRegistrationToken::class, $message);
        $this->assertSame('token', $message->token());
    }

    public function testCreateFromArrayWithoutToken()
    {
        $this->expectException(InvalidArgumentException::class);
        MessageToRegistrationToken::fromArray([]);
    }
}

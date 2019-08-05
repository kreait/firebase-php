<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Messaging\MessageToRegistrationToken;
use Kreait\Firebase\Tests\Integration\MessageTestCase;

/**
 * @internal
 */
class MessageToRegistrationTokenTest extends MessageTestCase
{
    /**
     * @var string|null
     */
    private $token;

    protected function setUp()
    {
        parent::setUp();

        $this->token = self::$registrationTokens[0] ?? null;
        $this->fullMessageData['token'] = $this->token;
    }

    public function testWithoutToken()
    {
        unset($this->fullMessageData['token']);
        $this->expectException(InvalidArgumentException::class);
        MessageToRegistrationToken::fromArray($this->fullMessageData);
    }

    public function testSendEmptyMessage()
    {
        if (!$this->token) {
            $this->markTestSkipped('No registration token available');
        }

        $this->assertSuccessfulMessage(MessageToRegistrationToken::create($this->token));
    }

    public function testSendFullMessage()
    {
        if (!$this->token) {
            $this->markTestSkipped('No registration token available');
        }

        $message = MessageToRegistrationToken::fromArray($this->fullMessageData);
        $this->assertSuccessfulMessage($message);
    }
}

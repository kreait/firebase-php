<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Messaging;

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
        $this->fullMessageData['token'] = $this->token;
    }

    public function testSendEmptyMessage()
    {
        $this->markTestSkipped('No valid registration token available yet');
        $this->assertSuccessfulMessage(MessageToRegistrationToken::create($this->token));
    }

    public function testSendFullMessage()
    {
        $this->markTestSkipped('No valid registration token available yet');
        parent::testSendFullMessage();
    }
}

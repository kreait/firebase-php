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

        $this->condition = "'dogs' in topics || 'cats' in topics";
        $this->fullMessageData['condition'] = $this->condition;
    }

    public function testSendEmptyMessage()
    {
        $this->assertSuccessfulMessage(ConditionalMessage::create($this->condition));
    }
}

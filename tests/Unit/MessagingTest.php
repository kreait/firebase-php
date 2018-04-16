<?php

namespace Kreait\Firebase\Tests\Unit;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\ApiClient;
use Kreait\Firebase\Messaging\MessageFactory;
use Kreait\Firebase\Tests\UnitTestCase;

class MessagingTest extends UnitTestCase
{
    /**
     * @var Messaging
     */
    private $messaging;

    /**
     * @var ApiClient
     */
    private $apiClient;

    /**
     * @var MessageFactory
     */
    private $messageFactory;

    protected function setUp()
    {
        $this->messageFactory = new MessageFactory();
        $this->apiClient = $this->createMock(ApiClient::class);

        $this->messaging = new Messaging($this->apiClient, $this->messageFactory);
    }

    public function testSendInvalidObject()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->messaging->send(new \stdClass());
    }

    public function testSendInvalidArray()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->messaging->send([]);
    }
}

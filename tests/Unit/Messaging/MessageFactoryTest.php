<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Kreait\Firebase\Messaging\ConditionalMessage;
use Kreait\Firebase\Messaging\MessageFactory;
use Kreait\Firebase\Messaging\MessageToRegistrationToken;
use Kreait\Firebase\Messaging\MessageToTopic;
use Kreait\Firebase\Tests\UnitTestCase;
use Kreait\Firebase\Util\JSON;

class MessageFactoryTest extends UnitTestCase
{
    /**
     * @var MessageFactory
     */
    private $factory;

    protected function setUp()
    {
        $this->factory = new MessageFactory();
    }

    /**
     * @param array $data
     * @dataProvider validDataProvider
     */
    public function testCreateFromArray(array $data)
    {
        $message = $this->factory->fromArray($data);

        $this->assertEquals($data, $message->jsonSerialize());
    }

    public function validDataProvider(): array
    {
        return [
            [JSON::decode(JSON::encode(MessageToTopic::create('my-topic')), true)],
            [JSON::decode(JSON::encode(MessageToRegistrationToken::create('my-token')), true)],
            [JSON::decode(JSON::encode(ConditionalMessage::create('my-condition')), true)],
        ];
    }
}

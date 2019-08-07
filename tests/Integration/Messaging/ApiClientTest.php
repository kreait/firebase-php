<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Messaging;

use Kreait\Firebase\Messaging\ApiClient;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Message;
use Kreait\Firebase\Tests\Integration\MessageTestCase;
use Kreait\Firebase\Tests\IntegrationTestCase;

/**
 * @internal
 */
final class ApiClientTest extends IntegrationTestCase
{
    /**
     * @var ApiClient
     */
    private $client;

    /** @var Message */
    private $validMessage;

    protected function setUp()
    {
        $projectId = self::$serviceAccount->getSanitizedProjectId();

        $httpClient = self::$factory->createApiClient([
            'base_uri' => 'https://fcm.googleapis.com/v1/projects/'.$projectId,
        ]);

        $this->client = new ApiClient($httpClient);

        $messageData = MessageTestCase::createFullMessageData();
        $messageData['condition'] = "'dogs' in topics || 'cats' in topics";

        $this->validMessage = CloudMessage::fromArray($messageData);
    }

    public function testSendMessage()
    {
        $this->client->sendMessage($this->validMessage);
        $this->addToAssertionCount(1);
    }

    public function testSendMessageAsync()
    {
        $this->client->sendMessageAsync($this->validMessage)->wait();
        $this->addToAssertionCount(1);
    }

    public function testValidateMessage()
    {
        $this->client->validateMessage($this->validMessage);
        $this->addToAssertionCount(1);
    }

    public function testValidateMessageAsync()
    {
        $this->client->validateMessageAsync($this->validMessage)->wait();
        $this->addToAssertionCount(1);
    }
}
